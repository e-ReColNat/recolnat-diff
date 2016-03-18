<?php

namespace AppBundle\Controller;

use AppBundle\Business\DiffHandler;
use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Manager\RecolnatServer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Description of AjaxDiffsController
 *
 * @author tpateffoz
 */
class BackendController extends Controller
{

    /**
     * @Route("/{institutionCode}/{collectionCode}/export/{type}/", name="export")
     * @param string  $type
     * @param Request $request
     * @param string  $institutionCode
     * @param string  $collectionCode
     * @return JsonResponse
     * @throws \Exception
     */
    public function exportAction($type, $institutionCode, $collectionCode, Request $request)
    {
        /** @var ExportPrefs $exportPrefs */
        $exportPrefs = unserialize($request->get('exportPrefs'));
        if (!($exportPrefs instanceof ExportPrefs)) {
            throw new \Exception('parameters must be an instance of ExportPrefs');
        }
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $file = null;
        switch ($type) {
            case 'dwc':
                $file = $exportManager->export('dwc', $exportPrefs);
                break;
            case 'csv':
                $file = $exportManager->export('csv', $exportPrefs);
                break;
        }
        $response = new JsonResponse();
        if (is_null($file)) {
            $message = $this->get('translator')->trans('export.probleme');
            $response->setContent($message);

            $this->addFlash('error', $message);
            $response->setStatusCode(400);
            return $response;
        }
        return $response->setContent(['file' => urlencode($file)]);
    }

    /**
     * @Route("/{institutionCode}/{collectionCode}/searchDiff/", name="searchDiff", options={"expose"=true})
     * @param string  $institutionCode
     * @param string  $collectionCode
     * @return Response
     */
    public function searchDiffAction($institutionCode, $collectionCode)
    {
        $collection = $this->getDoctrine()->getManager()
            ->getRepository('AppBundle:Collection')->findOneBy(['collectioncode' => $collectionCode]);


        $diffManager = $this->get('diff.manager');
        $diffComputer = $this->get('diff.computer');
        $diffManager->init($collection, $this->getParameter('export_path'));
        $response = new StreamedResponse();

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $diffManager->init($collection, $this->getParameter('export_path'));

        $diffHandler = new DiffHandler($this->getParameter('export_path').'/'.$institutionCode);
        $diffHandler->setCollectionCode($collectionCode);

        $response->setCallback(function() use ($diffManager, $diffComputer, $diffHandler) {
            $server = new RecolnatServer();
            $specimenCodes = [];

            // Nb total d'étapes :  Search / Compute pour chaque entité
            // +1 étape sauvegarde
            $server->steps->send(count($diffManager::ENTITIES_NAME)*2 +1);
            $countStep = 0 ;
            foreach ($diffManager::ENTITIES_NAME as $entityName) {

                $server->step->send(json_encode(['count'=> $countStep++, 'step'=>'search '.$entityName]));
                $specimenCodes[$entityName] = $diffManager->getDiff($entityName);
                $server->step->send(json_encode(['count'=> $countStep++, 'step'=>'compute '.$entityName]));

                $diffComputer->setSpecimenCodes($specimenCodes);
                $diffComputer->computeClassname($entityName);
            }

            $datas = array_merge($diffComputer->getDiffs(),
                [
                    'stats' => $diffComputer->getAllStats(),
                    'lonesomeRecords' => $diffComputer->getLonesomeRecords(),
                    'statsLonesomeRecords' => $diffComputer->getStatsLonesomeRecords()
                ]);

            $server->step->send(json_encode(['count'=> $countStep++, 'step'=>'save']));
            $diffHandler->saveDiffs($datas);
            $server->step->send(json_encode(['count'=> $countStep++, 'step'=>'done']));
            $server->close->send(true);
        });

        $response->send();
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
            $response->headers->set('Content-Disposition', 'attachment; collectionCode="'.basename($path).'"');
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
     * @Route("/setChoice/{institutionCode}/{collectionCode}", name="setChoice", options={"expose"=true})
     * @param Request $request
     * @param string  $institutionCode
     * @param string  $collectionCode
     * @return JsonResponse
     */
    public function setChoiceAction(Request $request, $institutionCode, $collectionCode)
    {
        $choices = $request->get('choices');
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $exportManager->setChoices($choices);

        $response = new JsonResponse();
        $response->setData(['choices' => $exportManager->sessionHandler->getChoices()]);

        $this->setFlashMessageForChoices($choices);
        return $response;
    }

    /**
     * @Route("/setChoices/{institutionCode}/{collectionCode}", name="setChoices", options={"expose"=true})
     * @param Request $request
     * @param string  $institutionCode
     * @param string  $collectionCode
     * @return JsonResponse
     */
    public function setChoicesAction(Request $request, $institutionCode, $collectionCode)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $inputOrigin = $request->get('origin', null);
        $inputSpecimens = $request->get('specimens', null);
        $inputClassesName = $request->get('classesName', []);
        $page = $request->get('page', null);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);
        $selectedSpecimens = json_decode($request->get('selectedSpecimens', null));
        $selectedClassName = $request->get('selectedClassName', null);
        $type = json_decode($request->get('type', null));
        $choices = [];
        $items = [];

        list($specimensWithChoices, $specimensWithoutChoices) = [[], []];
        if ($type == 'choices') {
            $specimensWithChoices = array_keys($exportManager->getSessionHandler()->getChoicesBySpecimenCode());
        }
        if ($type == 'todo') {
            $specimensWithoutChoices = $exportManager->getSessionHandler()->getChoices();
        }
        if (!is_null($institutionCode) && !is_null($inputOrigin) && !is_null($inputSpecimens)) {
            $diffs = $exportManager->getDiffs($request, $selectedClassName, $specimensWithChoices,
                $specimensWithoutChoices);
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
                        foreach ($selectedSpecimens as $specimenCode) {
                            if (isset($diffs['datas'][$specimenCode])) {
                                $items[$specimenCode] = $diffs['datas'][$specimenCode];
                            }
                        }
                    }
                    break;
            }
            $choices = DiffHandler::formatItemsToChoices($items, $diffs, $inputClassesName, $inputOrigin, $choices);
        }

        $exportManager->setChoices($choices);

        $response = new JsonResponse();
        $this->setFlashMessageForChoices($choices);
        $response->setData(['choices' => $exportManager->getSessionHandler()->getChoices()]);

        return $response;
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
     * @Route("/deleteChoices/{institutionCode}/{collectionCode}", name="deleteChoices", options={"expose"=true})
     * @param string $institutionCode
     * @param string $collectionCode
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteChoicesAction($institutionCode, $collectionCode)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $diffHandler = $exportManager->getDiffHandler();
        $choices = $diffHandler->getChoices();
        $choices->deleteChoices();
        $this->get('session')->clear();
        $response = new JsonResponse();
        $response->setData(['deleteChoices' => true]);
        return $response;
    }

    /**
     * @Route("/deleteDiffs/{institutionCode}/{collectionCode}", name="deleteDiffs", options={"expose"=true})
     * @param string $institutionCode
     * @param string $collectionCode
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteDiffsAction($institutionCode, $collectionCode)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $diffHandler = $exportManager->getDiffHandler();
        $diffs = $diffHandler->getDiffs();
        $diffs->deleteChoices();
        $exportManager->launchDiffProcess();
        $this->get('session')->clear();
        $response = new JsonResponse();
        $response->setData(['deleteDiffs' => true]);
        return $response;
    }

}
