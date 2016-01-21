<?php

namespace AppBundle\Controller;

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
     * @Route("/{institutionCode}/{collectionCode}/export/dwc", name="exportDwc")
     */
    public function exportDwcAction($institutionCode, $collectionCode)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $dwc = $exportManager->getDwc();
        return new JsonResponse(['file' => urlencode($dwc)]);
    }

    /**
     * @Route("/{institutionCode}/{collectionCode}/export/csv", name="exportCsv")
     */
    public function exportCsvAction($institutionCode, $collectionCode)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $csv = $exportManager->getCsv();
        return new JsonResponse(['file' => urlencode($csv)]);
    }

    /**
     * @Route("/download/{path}", name="download", options={"expose"=true})
     */
    public function downloadAction($path = "")
    {
        $response = new Response();
        if ($path != '') {
            $path = urldecode($path);
            $response->setContent(file_get_contents($path));
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', 'attachment; collectionCode="' . basename($path) . '"');
        }

        return $response;
    }

    /**
     * @Route("/setChoice/{institutionCode}/{collectionCode}", name="setChoice", options={"expose"=true})
     * @param Request $request
     * @param string $institutionCode
     * @param array choices
     */
    public function setChoiceAction(Request $request, $institutionCode, $collectionCode)
    {
        $choices = $request->get('choices');
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $exportManager->setChoices($choices);
        $exportManager->saveChoices();

        $response = new JsonResponse();
        $response->setData(['choices' => $exportManager->getChoices()]);

        $this->setFlashMessageForChoices($choices);
        return $response;
    }

    /**
     * @Route("/setChoices/{institutionCode}/{collectionCode}", name="setChoices", options={"expose"=true})
     * @param Request $request
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
            $specimensWithChoices = array_keys($exportManager->getChoicesBySpecimenCode());
        }
        if ($type == 'todo') {
            $specimensWithoutChoices = $exportManager->getChoices();
        }
        if (!is_null($institutionCode) && !is_null($inputOrigin) && !is_null($inputSpecimens)) {
            $diffs = $exportManager->getDiffs($request, $selectedClassName, $specimensWithChoices, $specimensWithoutChoices);
            switch ($inputSpecimens) {
                case 'page' :
                    $paginator = $this->get('knp_paginator');
                    $pagination = $paginator->paginate($diffs['summary'], $page, $maxItemPerPage);
                    $items = $pagination->getItems();
                    break;
                case 'allDatas' :
                    $items = $diffs['summary'];
                    break;
                case 'selectedSpecimens' :
                    if (!is_null($selectedSpecimens)) {
                        foreach ($selectedSpecimens as $specimenCode) {
                            if (isset($diffs['summary'][$specimenCode])) {
                                $items[$specimenCode] = $diffs['summary'][$specimenCode];
                            }
                        }
                    }
                    break;
            }
            if (count($items) > 0) {
                foreach ($items as $specimenCode => $row) {
                    foreach ($row['classes'] as $className => $data) {
                        $rowClass = $diffs['summary'][$specimenCode]['classes'][$className];
                        $relationId = $rowClass['id'];
                        foreach ($rowClass['fields'] as $fieldName => $rowFields) {
                            $doUpdate = false;
                            if (in_array(strtolower($className), $inputClassesName)) {
                                $doUpdate = true;
                            }
                            if ($doUpdate) {
                                $choices[] = [
                                    "className" => $className,
                                    "fieldName" => $fieldName,
                                    "relationId" => $relationId,
                                    "choice" => $inputOrigin,
                                    "specimenCode" => $specimenCode,
                                ];
                            }
                        }
                    }
                }
            }
        }

        $exportManager->setChoices($choices);

        $response = new JsonResponse();
        $this->setFlashMessageForChoices($choices);
        $response->setData(['choices' => $exportManager->getChoices()]);

        return $response;
    }

    private function setFlashMessageForChoices(array $choices)
    {
        $translator = $this->get('translator');
        $message = $translator->transChoice('modification.effectuee', count($choices), array('%nbModif%' => count($choices)));
        if (count($choices) == 0) {
            $this->addFlash('warning', $message);
        } else {
            $this->addFlash('success', $message);
        }
    }

    /**
     * @Route("/deleteChoices/{institutionCode}/{collectionCode}", name="deleteChoices", options={"expose"=true})
     * @param Request $request
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
     * @param Request $request
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
