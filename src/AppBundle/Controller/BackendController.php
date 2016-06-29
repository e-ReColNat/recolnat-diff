<?php

namespace AppBundle\Controller;

use AppBundle\Business\DiffHandler;
use AppBundle\Business\Exporter\ExportPrefs;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of AjaxDiffsController
 *
 * @author tpateffoz
 */
class BackendController extends Controller
{

    /**
     * @Route("{institutionCode}/{collectionCode}/export/{type}/", name="export", requirements={"type": "csv|dwc"})
     * @param string  $type
     * @param Request $request
     * @param string  $collectionCode
     * @param string  $institutionCode
     * @return JsonResponse
     * @throws \Exception
     */
    public function exportAction($institutionCode, $collectionCode, $type, Request $request)
    {
        set_time_limit(0);
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $this->getUser());
        /** @var ExportPrefs $exportPrefs */
        $exportPrefs = unserialize($request->get('exportPrefs'));
        if (!($exportPrefs instanceof ExportPrefs)) {
            throw new \Exception('parameters must be an instance of ExportPrefs');
        }
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollection($collection);
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
     * @Route("/setChoice/{institutionCode}/{collectionCode}", name="setChoice", options={"expose"=true})
     * @param Request $request
     * @param string  $institutionCode
     * @param string  $collectionCode
     * @return JsonResponse
     */
    public function setChoiceAction(Request $request, $institutionCode, $collectionCode)
    {
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $this->getUser());
        $choices = $request->get('choices');
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollection($collection);
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
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $this->getUser());
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollection($collection);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);

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
        if (!is_null($inputOrigin) && !is_null($inputSpecimens)) {
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
     * @Route("/deleteChoices/{institutionCode}/{collectionCode}", name="deleteChoices", options={"expose"=true})
     * @param string $institutionCode
     * @param string $collectionCode
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteChoicesAction($institutionCode, $collectionCode)
    {
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $this->getUser());
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollection($collection);
        $diffHandler = $exportManager->getDiffHandler();
        $choices = $diffHandler->getChoicesFile();
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
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $this->getUser());
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollection($collection);
        $diffHandler = $exportManager->getDiffHandler();
        $diffs = $diffHandler->getDiffsFile();
        $diffs->deleteChoices();
        $this->get('session')->clear();
        $response = new JsonResponse();
        $response->setData(['deleteDiffs' => true]);

        return $response;
    }

    /**
     * @Route("selectedSpecimen/{institutionCode}/{collectionCode}", name="selectedSpecimen")
     * @param string  $institutionCode
     * @param string  $collectionCode
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function selectedSpecimenAction(Request $request, $institutionCode, $collectionCode)
    {
        $session = $this->get('session');
        $action = $request->get('action');
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $this->getUser());

        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollection($collection);

        $catalogNumber = $request->get('catalogNumber');
        $selectedSpecimensHandler = $exportManager->getSelectedSpecimenHandler();
        if ($action == 'add') {
            $selectedSpecimensHandler->add($catalogNumber);
        } else {
            $selectedSpecimensHandler->remove($catalogNumber);
        }
        $data = $selectedSpecimensHandler->getData();
        $session->set('selectSpecimens', $data);
        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }
}
