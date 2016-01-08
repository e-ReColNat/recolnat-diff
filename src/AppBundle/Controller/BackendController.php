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
     * @Route("/{institutionCode}/{filename}/export/dwc", name="exportDwc")
     */
    public function exportDwcAction($institutionCode, $filename)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $filename);
        $dwc = $exportManager->getDwc();
        return new JsonResponse(['file' =>  urlencode($dwc)]);
    }
    
     /**
     * @Route("/{institutionCode}/{filename}/export/csv", name="exportCsv")
     */
    public function exportCsvAction($institutionCode, $filename)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $filename);
        $csv = $exportManager->getCsv();
        return new JsonResponse(['file' =>  urlencode($csv)]);
    }
    
     /**
     * @Route("/download/{path}", name="download", options={"expose"=true})
     */
    public function downloadAction($path="")
    {
        $response = new Response();
        if ($path != '') {
            $path=  urldecode($path);
            $response->setContent(file_get_contents($path));
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($path). '"');
        }

        return $response;
    }
    
    /**
     * @Route("/setChoice/{institutionCode}/{filename}", name="setChoice", options={"expose"=true})
     * @param Request $request
     * @param string $institutionCode
     * @param array choices
     */
    public function setChoiceAction(Request $request, $institutionCode, $filename)
    {
        $choices = $request->get('choices');
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $filename);
        $exportManager->setChoices($choices);
        $exportManager->saveChoices();

        $response = new JsonResponse();
        $response->setData(['choices'=>$exportManager->getChoices()]) ;
        
        $this->setFlashMessageForChoices($choices) ;
        return $response;
    }
    
    /**
     * @Route("/setChoices/{institutionCode}/{filename}", name="setChoices", options={"expose"=true})
     * @param Request $request
     */
    public function setChoicesAction(Request $request, $institutionCode, $filename)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $filename);
        $selectLevel1 = $request->get('selectLevel1', null);
        $selectLevel2 = $request->get('selectLevel2', null);
        $selectLevel3 = $request->get('selectLevel3', []);
        $page = $request->get('page', null);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request) ;
        $selectedSpecimens = json_decode($request->get('selectedSpecimens', null));
        $selectedClassName = $request->get('selectedClassName', null);
        $type = json_decode($request->get('type', null));
        $choices = [];
        $items = [];
        
        list($specimensWithChoices,$specimensWithoutChoices)=[[],[]];
        if ($type == 'choices') {
            $specimensWithChoices=array_keys($exportManager->getChoicesBySpecimenId()) ;
        }
        if ($type == 'todo') {
            $specimensWithoutChoices=$exportManager->getChoices() ;
        }
        if (!is_null($institutionCode) && !is_null($selectLevel1) && !is_null($selectLevel2)) {
            list($specimensCode, $diffs, $stats) = $exportManager->getSpecimenIdsAndDiffsAndStats($request, $selectedClassName, $specimensWithChoices, $specimensWithoutChoices);
            switch ($selectLevel2) {
                case 'page' :
                    $paginator = $this->get('knp_paginator');
                    $pagination = $paginator->paginate($stats['summary'], $page, $maxItemPerPage);
                    $items = $pagination->getItems();
                    break;
                case 'allDatas' :
                    $items = $stats['summary'] ;
                    break;
                case 'selectedSpecimens' :
                    if (!is_null($selectedSpecimens)) {
                        foreach ($selectedSpecimens as $specimenCode) {
                            if (isset($stats['summary'][$specimenCode]))  {
                                $items[$specimenCode] = $stats['summary'][$specimenCode] ;
                            }
                        }
                    }
                    break;
            }
            if (count($items) > 0) {
                foreach ($items as $specimenCode=>$row) {
                    foreach ($row as $className => $data) {
                        $rowClass = $stats['classes'][$className][$specimenCode] ;
                        foreach ($rowClass as $relationId => $rowFields) {
                            foreach($rowFields as $fieldName=>$dataFields) {
                                $doUpdate = false ;
                                if (in_array(strtolower($className), $selectLevel3)) {
                                    $doUpdate = true ;
                                }
                                if ($doUpdate) {
                                    $choices[] = [
                                        "className" => $className,
                                        "fieldName" => $fieldName,
                                        "relationId" => $relationId,
                                        "choice" => $selectLevel1,
                                        "specimenId" => $specimenCode,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        $exportManager->setChoices($choices);

        $response = new JsonResponse();
        $this->setFlashMessageForChoices($choices) ;
        $response->setData(['choices'=>$exportManager->getChoices()]) ;
        
        return $response;
    }
    
    private function setFlashMessageForChoices(array $choices) {
        $translator = $this->get('translator');
        $message = $translator->transChoice('modification.effectuee', count($choices),array('%nbModif%'=>count($choices)));
        if (count($choices) == 0) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                $message
            );
        }
        else {
            $this->get('session')->getFlashBag()->add(
                'success',
                $message
            );
        }
    }
}
