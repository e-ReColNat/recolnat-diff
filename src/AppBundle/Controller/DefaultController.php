<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    const MAX_SPECIMEN_PAGE = 5 ;

    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request) {
        $institutionCode = 'MHNAIX';
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode);

        list($specimensCode, $diffs, $stats) = $this->getSpecimenIdsAndDiffsAndStats($request, $institutionCode);
        
        $choices=$exportManager->getChoicesForDisplay();
        $sortedStats = $stats['classes'] ;
        $total = [] ;
        $totalChoices = [] ;
     
        $callbackCount = function($value, $className) use (&$total){
            if (is_array($value)) {
                if (!isset($total[$className])) { $total[$className]=0; }
                foreach ($value as $row) {
                    foreach ($row as $fields) {
                        $total[$className] += count($fields) ;
                    }
                }
            }
        };
        $callbackCountChoices = function($value, $className) use (&$totalChoices){
            if (is_array($value)) {
                if (!isset($total[$className])) { $totalChoices[$className]=0; }
                foreach ($value as $row) {
                    foreach ($row as $fields) {
                        $totalChoices[$className] += count($fields) ;
                    }
                }
            }
        };
        array_walk($sortedStats, $callbackCount);
        array_walk($choices, $callbackCountChoices);
        uasort($sortedStats, function($a, $b) {
            return count(array_values($a)) < count(array_values($b)) ? 1 : -1;
        });

        $total['sum'] = array_sum($total);
        $totalChoices['sum'] = array_sum($totalChoices);
        
        return $this->render('default/index.html.twig', array(
            'institutionCode' => $institutionCode,
            'stats' => $sortedStats,
            'diffs' => $diffs[$institutionCode],
            'totalChoices' => $totalChoices,
            'total'=> $total,
        ));
    }
    /**
     * @Route("{institutionCode}/diffs/{selectedClassName}/{page}", name="diffs", defaults={"selectedClassName" = "all", "page" = 1}, requirements={
    *     "page": "\d+"
    * })
     */
    public function diffsAction(Request $request, $institutionCode, $selectedClassName = "all", $page = 1)
    {
        $maxItemPerPage = $request->get('maxItemPerPage', self::MAX_SPECIMEN_PAGE) ;
        
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode);

        list($specimensCode, $diffs, $stats) = $this->getSpecimenIdsAndDiffsAndStats($request, $institutionCode, $selectedClassName);

        $specimenRepositoryRecolnat = $this->getDoctrine()
                ->getRepository('AppBundle\Entity\Specimen');
        $specimenRepositoryInstitution = $this->getDoctrine()
                ->getRepository('AppBundle\Entity\Specimen', 'diff');

        $specimensRecolnat = $specimenRepositoryRecolnat
                ->findBySpecimenCodes($specimensCode);
        $specimensInstitution = $specimenRepositoryInstitution
                ->findBySpecimenCodes($specimensCode);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $stats['summary'], $page, $maxItemPerPage
        );

        return $this->render('default/viewDiffs.html.twig', array(
            'institutionCode' => $institutionCode,
            'stats' => $stats,
            'diffs' => $diffs[$institutionCode],
            'specimensRecolnat' => $specimensRecolnat,
            'specimensInstitution' => $specimensInstitution,
            'pagination' => $pagination,
            'choicesFacets' => $exportManager->getChoices(),
            'choices' => $exportManager->getChoicesForDisplay(),
            'maxItemPerPage'=>$maxItemPerPage,
            'selectedClassName' => $selectedClassName,
        ));
    }
    
    /**
     * @Route("{institutionCode}/choices/{selectedClassName}/{page}", name="choices", defaults={"selectedClassName" = "all", "page" = 1}, requirements={
    *     "page": "\d+"
    * })
     */
    public function viewChoicesAction(Request $request, $institutionCode, $selectedClassName = "all", $page = 1)
    {
        $maxItemPerPage = $request->get('maxItemPerPage', self::MAX_SPECIMEN_PAGE) ;
        
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode);

        $specimensWithChoices=$exportManager->getChoicesBySpecimenId() ;
        list($specimensCode, $diffs, $stats) = $this->getSpecimenIdsAndDiffsAndStats($request, $institutionCode, $selectedClassName, array_keys($specimensWithChoices));

        $specimenRepositoryRecolnat = $this->getDoctrine()
                ->getRepository('AppBundle\Entity\Specimen');
        $specimenRepositoryInstitution = $this->getDoctrine()
                ->getRepository('AppBundle\Entity\Specimen', 'diff');

        $specimensRecolnat = $specimenRepositoryRecolnat
                ->findBySpecimenCodes($specimensCode);
        $specimensInstitution = $specimenRepositoryInstitution
                ->findBySpecimenCodes($specimensCode);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $stats['summary'], $page, $maxItemPerPage
        );

        return $this->render('default/viewDiffs.html.twig', array(
            'institutionCode' => $institutionCode,
            'stats' => $stats,
            'diffs' => $diffs[$institutionCode],
            'specimensRecolnat' => $specimensRecolnat,
            'specimensInstitution' => $specimensInstitution,
            'pagination' => $pagination,
            'choicesFacets' => $exportManager->getChoices(),
            'choices' => $exportManager->getChoicesForDisplay(),
            'maxItemPerPage'=>$maxItemPerPage,
            'selectedClassName' => $selectedClassName,
        ));
    }
    
    /**
     * @Route("{institutionCode}/todo/{selectedClassName}/{page}", name="todo", defaults={"selectedClassName" = "all", "page" = 1}, requirements={
    *     "page": "\d+"
    * })
     */
    public function todoAction(Request $request, $institutionCode, $selectedClassName = "all", $page = 1)
    {
        $maxItemPerPage = $request->get('maxItemPerPage', self::MAX_SPECIMEN_PAGE) ;
        
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode);

        $specimensWithChoices=$exportManager->getChoices() ;
        list($specimensCode, $diffs, $stats) = $this->getSpecimenIdsAndDiffsAndStats($request, $institutionCode, $selectedClassName, [],  $specimensWithChoices);

        $specimenRepositoryRecolnat = $this->getDoctrine()
                ->getRepository('AppBundle\Entity\Specimen');
        $specimenRepositoryInstitution = $this->getDoctrine()
                ->getRepository('AppBundle\Entity\Specimen', 'diff');

        $specimensRecolnat = $specimenRepositoryRecolnat
                ->findBySpecimenCodes($specimensCode);
        $specimensInstitution = $specimenRepositoryInstitution
                ->findBySpecimenCodes($specimensCode);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $stats['summary'], $page, $maxItemPerPage
        );

        return $this->render('default/viewDiffs.html.twig', array(
            'institutionCode' => $institutionCode,
            'stats' => $stats,
            'diffs' => $diffs[$institutionCode],
            'specimensRecolnat' => $specimensRecolnat,
            'specimensInstitution' => $specimensInstitution,
            'pagination' => $pagination,
            'choicesFacets' => $exportManager->getChoices(),
            'choices' => $exportManager->getChoicesForDisplay(),
            'maxItemPerPage'=>$maxItemPerPage,
            'selectedClassName' => $selectedClassName,
        ));
    }
    
    /**
     * @param Request $request
     * @param String $institutionCode
     * @param String $selectedClassName
     * @param Array $specimensWithChoices
     * @param Array $choicesToRemove
     * @return array $array
     */
    private function getSpecimenIdsAndDiffsAndStats(Request $request, $institutionCode, $selectedClassName=null, $specimensWithChoices=[], $choicesToRemove=[])
    {
        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->get('session');

        if ($selectedClassName == "all") {$selectedClassName = null;}
        if (!is_null($request->query->get('reset', null))) {
            $session->clear();
        }
        /* @var $diffManager \AppBundle\Manager\DiffManager */
        $diffManager = $this->get('diff.manager');
        $results =$diffManager->init($institutionCode, [$selectedClassName], $specimensWithChoices, $choicesToRemove);
        list ($diffs, $specimensCode, $stats) = [$results['diffs'],$results['specimensCode'],$results['stats']] ;
        $session->set('diffs', $diffs);
        $session->set('specimensCode', $specimensCode);
        $session->set('stats', $stats);
        return [$specimensCode, $diffs, $stats];
    }
    /**
     * @Route("/setChoice/{institutionCode}", name="setChoice", options={"expose"=true})
     * @param Request $request
     * @param string $institutionCode
     * @param array choices
     */
    public function setChoiceAction(Request $request, $institutionCode)
    {
        $choices = $request->get('choices');
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode);
        $exportManager->setChoices($choices);
        $exportManager->saveChoices();

        $response = new JsonResponse();
        $response->setData(['choices'=>$exportManager->getChoices()]) ;
        
        $this->setFlashMessageForChoices($choices) ;
        return $response;
    }
    /**
     * @Route("/setChoices/", name="setChoices", options={"expose"=true})
     * @param Request $request
     */
    public function setChoicesAction(Request $request)
    {

        $selectLevel1 = $request->get('selectLevel1', null);
        $selectLevel2 = $request->get('selectLevel2', null);
        $selectLevel3 = $request->get('selectLevel3', []);
        $page = $request->get('page', null);
        $maxItemPerPage = $request->get('maxItemPerPage', self::MAX_SPECIMEN_PAGE);
        $institutionCode = $request->get('institutionCode', null);
        $selectedSpecimens = json_decode($request->get('selectedSpecimens', null));
        $selectedClassName = json_decode($request->get('selectedClassName', null));
        $choices = [];
        $items = [];
        
        if (!is_null($institutionCode) && !is_null($selectLevel1) && !is_null($selectLevel2)) {
            list($specimensCode, $diffs, $stats) = $this->getSpecimenIdsAndDiffsAndStats($request, $institutionCode, $selectedClassName);
            switch ($selectLevel2) {
                case 'page' :
                    $paginator = $this->get('knp_paginator');
                    $pagination = $paginator->paginate(
                            $stats['summary'], $page, $maxItemPerPage
                    );
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
                                $flag = false ;
                                if ($selectLevel3 =='allClasses' || in_array(strtolower($className), $selectLevel3)) {
                                    $flag = true ;
                                }
                                if ($flag) {
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

        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode);
        $exportManager->setChoices($choices);
        $exportManager->saveChoices();

        $response = new JsonResponse();
        $response->setData(['choices'=>$exportManager->getChoices()]) ;
        $this->setFlashMessageForChoices($choices) ;
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
    /**
     * @Route("/export/{institutionCode}", name="export")
     */
    public function exportAction(Request $request, $institutionCode)
    {
        $converterPath = realpath($this->getParameter('converter_path')) ;
        
        exec(sprintf('/bin/sh %s --context_param global_filename_json=%s.json global_filename_choices=choices_%s.json',$converterPath,$institutionCode,$institutionCode));
        return new Response('done');
    }


    /**
     * @Route("/test/", name="test")
     */
    public function testAction()
    {
        /**
         * "className": "Recolte",
        "fieldName": "smonth",
        "relationId": "32F9CDBC488F49E68249D751618D2DB8",
        "choice": "recolnat",
         */
        $institutionCode = 'MHNAIX';
        $row[]= [
            "className"=> "Determination",
            "fieldName"=> "dateidentified",
            "relationId"=> "CC3C34F984BC4BF09ADC9C689AA4B01A",
            "choice"=> "recolnat",
            "specimenId"=> "MHNAIXAIXAIX028625"
        ] ;
        /* @var $genericEntityManager \AppBundle\Manager\GenericEntityManager */
        //$genericEntityManager = $this->get('genericEntityManager') ;
        //$data = $genericEntityManager->getData($row['choice'], $row['className'], $row['fieldName'], $row['relationId']);
        //var_dump($data) ;
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode);
        $exportManager->setChoices($row);
        $exportManager->saveChoices();
        var_dump($exportManager->getChoices());
        return new Response() ;
    }

    /**
     * @Route("/translate/", name="translate", defaults={"_format": "xml"},)
     */
    public function translateAction()
    {
        $entitiesName = [
            'Specimen',
            'Bibliography',
            'Determination',
            'Localisation',
            'Recolte',
            'Stratigraphy',
            'Taxon'
        ];
        $translateFields = [];
        foreach ($entitiesName as $name) {
            $metadata = $this->getDoctrine()->getManager()->getMetadataFactory()
                    ->getMetadataFor('AppBundle:' . $name);
            $identifier = key(array_flip($metadata->getIdentifier()));
            $fields = array_flip($metadata->getFieldNames());
            unset($fields[$identifier]);
            $translateFields[$name] = array_flip($fields);
        }
        return $this->render('default/translate.xml.twig', array(
                    'translateFields' => $translateFields,
        ));
    }
    /**
     * @Route("/generateDiff/{institutionCode}/{compt}", name="generateDiff")
     */
    public function generateDiff($compt) 
    {
        /* @var $diffManager \AppBundle\Manager\DiffManager */
        $em = $this->get('doctrine')->getManager('diff');
        $diffManager = new \AppBundle\Manager\DiffManager($em) ;
        //$diffManager = $this->get('diff.manager');
        for ($i=1; $i<=$compt;$i++) {
            $diffManager->generateDiff(rand(1,5)) ;
        }
        $response = new Response();
        return $response;
    }
}
