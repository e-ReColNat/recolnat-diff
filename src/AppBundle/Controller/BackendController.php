<?php

namespace AppBundle\Controller;

use AppBundle\Business\DiffHandler;
use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Business\SelectedSpecimensHandler;
use AppBundle\Manager\DiffComputer;
use AppBundle\Manager\DiffManager;
use AppBundle\Manager\RecolnatServer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Description of AjaxDiffsController
 *
 * @author tpateffoz
 */
class BackendController extends Controller
{

    /**
     * @Route("{collectionCode}/export/{type}/", name="export", requirements={"type": "csv|dwc"})
     * @param string  $type
     * @param Request $request
     * @param string  $collectionCode
     * @return JsonResponse
     * @throws \Exception
     */
    public function exportAction($type, $collectionCode, Request $request)
    {
        /** @var ExportPrefs $exportPrefs */
        $exportPrefs = unserialize($request->get('exportPrefs'));
        if (!($exportPrefs instanceof ExportPrefs)) {
            throw new \Exception('parameters must be an instance of ExportPrefs');
        }
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);
        $file = $exportManager->export($type, $exportPrefs);
        $response = new JsonResponse();
        if (is_null($file)) {
            $message = $this->get('translator')->trans('export.probleme');
            $response->setContent($message);

            $this->addFlash('error', $message);
            $response->setStatusCode(400);

            return $response;
        }

        return $response->setData(['file' => urlencode($file)]);
    }

    /**
     * @Route("{collectionCode}/searchDiff/", name="searchDiff", options={"expose"=true})
     * @param string $collectionCode
     * @return Response
     */
    public function searchDiffAction($collectionCode)
    {
        $collection = $this->getDoctrine()->getManager()
            ->getRepository('AppBundle:Collection')->findOneBy(['collectioncode' => $collectionCode]);

        $institutionCode = $this->getUser()->getInstitutionCode();
        $diffManager = $this->get('diff.manager');
        $diffManager->init($collection);

        $diffComputer = $this->get('diff.computer');
        $diffComputer->setCollection($collection);

        $diffHandler = new DiffHandler($this->getParameter('export_path').'/'.$institutionCode);
        $diffHandler->setCollectionCode($collectionCode);

        $response = new StreamedResponse();

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');


        $this->searchDiffSetCallBack($response, $diffManager, $diffComputer, $diffHandler);

        $response->send();
    }

    /**
     * @param StreamedResponse $response
     * @param DiffManager      $diffManager
     * @param DiffComputer     $diffComputer
     * @param DiffHandler      $diffHandler
     */
    private function searchDiffSetCallBack($response, $diffManager, $diffComputer, $diffHandler)
    {
        $response->setCallback(function() use ($diffManager, $diffComputer, $diffHandler) {
            $server = new RecolnatServer();
            $catalogNumbers = [];

            // Nb total d'étapes :  Search / Compute pour chaque entité
            // +1 étape sauvegarde
            $server->steps->send(count($diffManager::ENTITIES_NAME) * 2 + 1);
            $countStep = 0;
            foreach ($diffManager::ENTITIES_NAME as $entityName) {

                $server->step->send(json_encode(['count' => $countStep++, 'step' => $entityName. ' : recherche']));
                $catalogNumbers[$entityName] = $diffManager->getDiff($entityName);
                $server->step->send(json_encode(['count' => $countStep++, 'step' => $entityName. ' : traitement']));

                $diffComputer->setCatalogNumbers($catalogNumbers);
                $diffComputer->computeClassname($entityName);
            }

            $datas = $diffComputer->getAllDatas();

            $server->step->send(json_encode(['count' => $countStep++, 'step' => 'save']));
            $diffHandler->saveDiffs($datas);
            $server->step->send(json_encode(['count' => $countStep++, 'step' => 'done']));
        });
    }

    /**
     * @Route("/download/{path}", name="download", options={"expose"=true})
     * @param string $path
     * @return Response
     */
    public function downloadAction($path = '')
    {
        $response = new Response();
        if ($path != '') {
            $path = urldecode($path);
            $response->setContent(file_get_contents($path));
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', 'attachment; filename="'.basename($path).'"');
        }

        return $response;
    }

    /**
     * @Route("/setmaxitem/{maxItem}", name="setmaxitem", options={"expose"=true})
     * @param int $maxItem
     * @return JsonResponse
     */
    public function setMaxItemAction($maxItem)
    {
        if (is_int((int) $maxItem) && in_array((int) $maxItem, $this->container->getParameter('maxitemperpage'))) {
            $this->get('session')->set('maxItemPerPage', $maxItem);
        } else {
            $this->get('session')->set('maxItemPerPage', $this->container->getParameter('maxitemperpage')[0]);
        }

        return new JsonResponse($this->get('session')->get('maxItemPerPage'));
    }

    /**
     * @Route("/setChoice/{collectionCode}", name="setChoice", options={"expose"=true})
     * @param Request $request
     * @param string  $collectionCode
     * @return JsonResponse
     */
    public function setChoiceAction(Request $request, $collectionCode)
    {
        $choices = $request->get('choices');
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);
        $exportManager->setChoices($choices);

        $response = new JsonResponse();
        $response->setData(['choices' => $exportManager->sessionHandler->getChoices()]);

        $this->setFlashMessageForChoices($choices);

        return $response;
    }

    /**
     * @Route("/setChoices/{collectionCode}", name="setChoices", options={"expose"=true})
     * @param Request $request
     * @param string  $collectionCode
     * @return JsonResponse
     */
    public function setChoicesAction(Request $request, $collectionCode)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);
        $institutionCode = $this->getUser()->getInstitutionCode();

        list($inputOrigin, $inputSpecimens, $inputClassesName, $page, $selectedSpecimens, $selectedClassName, $type) =
            $this->getParamsForSetChoices($request);
        $choices = [];
        $items = [];

        list($specimensWithChoices, $specimensWithoutChoices) = [[], []];
        if ($type == 'choices') {
            $specimensWithChoices = array_keys($exportManager->getSessionHandler()->getChoicesByCatalogNumber());
        }
        if ($type == 'todo') {
            $specimensWithoutChoices = $exportManager->getSessionHandler()->getChoices();
        }
        if (!is_null($institutionCode) && !is_null($inputOrigin) && !is_null($inputSpecimens)) {
            $diffs = $exportManager->getDiffs($request, $selectedClassName, $specimensWithChoices,
                $specimensWithoutChoices);
            $items = $this->getItemsForSetChoices($inputSpecimens, $diffs, $page, $maxItemPerPage,
                $selectedSpecimens, $items);
            $choices = DiffHandler::formatItemsToChoices($items, $diffs, $inputClassesName, $inputOrigin, $choices);
        }

        $exportManager->setChoices($choices);

        $response = new JsonResponse();
        $this->setFlashMessageForChoices($choices);
        $response->setData(['choices' => $exportManager->getSessionHandler()->getChoices()]);

        return $response;
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getParamsForSetChoices(Request $request)
    {
        $inputOrigin = $request->get('origin', null);
        $inputSpecimens = $request->get('specimens', null);
        $inputClassesName = $request->get('classesName', []);
        $page = $request->get('page', null);
        $selectedSpecimens = json_decode($request->get('selectedSpecimens', null));
        $selectedClassName = $request->get('selectedClassName', null);
        $type = json_decode($request->get('type', null));

        return array(
            $inputOrigin,
            $inputSpecimens,
            $inputClassesName,
            $page,
            $selectedSpecimens,
            $selectedClassName,
            $type
        );
    }

    /**
     * @param string  $inputSpecimens
     * @param array   $diffs
     * @param integer $page
     * @param integer $maxItemPerPage
     * @param array   $selectedSpecimens
     * @param array   $items
     * @return array
     */
    private function getItemsForSetChoices($inputSpecimens, $diffs, $page, $maxItemPerPage, $selectedSpecimens, $items)
    {
        switch ($inputSpecimens) {
            case 'page':
                $paginator = $this->get('knp_paginator');
                $pagination = $paginator->paginate($diffs['datas'], $page, $maxItemPerPage);
                $items = $pagination->getItems();
                break;
            case 'allDatas':
                $items = $diffs['datas'];
                break;
            case 'selectedSpecimens':
                if (!is_null($selectedSpecimens) && is_array($selectedSpecimens)) {
                    foreach ($selectedSpecimens as $catalogNumber) {
                        if (isset($diffs['datas'][$catalogNumber])) {
                            $items[$catalogNumber] = $diffs['datas'][$catalogNumber];
                        }
                    }
                }
                break;
        }

        return $items;
    }

    /**
     * @param array $choices
     */
    private function setFlashMessageForChoices(array $choices)
    {
        $translator = $this->get('translator');
        $message = $translator->transChoice('modification.effectuee', count($choices),
            array('%count%' => count($choices)));
        if (count($choices) == 0) {
            $this->addFlash('warning', $message);
        } else {
            $this->addFlash('success', $message);
        }
    }

    /**
     * @Route("/deleteChoices/{collectionCode}", name="deleteChoices", options={"expose"=true})
     * @param string $collectionCode
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteChoicesAction($collectionCode)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);
        $diffHandler = $exportManager->getDiffHandler();
        $choices = $diffHandler->getChoicesFile();
        $choices->deleteChoices();
        $this->get('session')->clear();
        $response = new JsonResponse();
        $response->setData(['deleteChoices' => true]);

        return $response;
    }

    /**
     * @Route("/deleteDiffs/{collectionCode}", name="deleteDiffs", options={"expose"=true})
     * @param string $collectionCode
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteDiffsAction($collectionCode)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);
        $diffHandler = $exportManager->getDiffHandler();
        $diffs = $diffHandler->getDiffsFile();
        $diffs->deleteChoices();
        $exportManager->launchDiffProcess();
        $this->get('session')->clear();
        $response = new JsonResponse();
        $response->setData(['deleteDiffs' => true]);

        return $response;
    }

    /**
     * @Route("selectedSpecimen/{collectionCode}", name="selectedSpecimen")
     * @param string $collectionCode
     * @param Request $request
     */
    public function selectedSpecimenAction(Request $request, $collectionCode)
    {
        $session = $this->get('session');
        $action = $request->get('action');
        $catalogNumber = $request->get('catalogNumber') ;
        $selectedSpecimensHandler = new SelectedSpecimensHandler($this->getUser()->getDataDirPath().$collectionCode);
        if ($action == 'add') {
            $selectedSpecimensHandler->add($catalogNumber);
        }
        else {
            $selectedSpecimensHandler->remove($catalogNumber);
        }
        $data = $selectedSpecimensHandler->getData();
        $session->set('selectSpecimens', $data);
        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }
}
