<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DefaultController extends Controller
{
    const MAX_SPECIMEN_PAGE = 5 ;

    /**
     * @Route("/", name="diff")
     */
    public function indexAction(Request $request)
    {
        $maxItemPerPage = $request->get('maxItemPerPage', self::MAX_SPECIMEN_PAGE) ;
        $institutionCode = 'MHNAIX';
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode);

        list($specimensCode, $diffs, $stats) = $this->getSpecimenIdsAndDiffsAndStats($request, $institutionCode);

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
                $stats['summary'], $request->query->getInt('page', 1), $maxItemPerPage
        );

        return $this->render('default/index.html.twig', array(
                    'institutionCode' => $institutionCode,
                    'stats' => $stats,
                    'diffs' => $diffs[$institutionCode],
                    'specimensRecolnat' => $specimensRecolnat,
                    'specimensInstitution' => $specimensInstitution,
                    'pagination' => $pagination,
                    'choicesFacets' => $exportManager->getChoices(),
                    'choices' => $exportManager->getChoicesForDisplay(),
                    'maxItemPerPage'=>$maxItemPerPage,
        ));
    }
    
    /**
     * @param Request $request
     * @param String $institutionCode
     * @param String $type
     * @return type $array
     */
    private function getSpecimenIdsAndDiffsAndStats(Request $request, $institutionCode)
    {
        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->get('session');

        if (!is_null($request->query->get('reset', null))) {
            $session->clear();
        }
        $diffs = $session->get('diffs');
        $specimensCode = $session->get('specimensCode');
        $stats = $session->get('stats');
        $filePath = realpath($this->getParameter('export_path')) . '/' . $institutionCode . '.json';
        if (is_null($diffs) || !isset($diffs[$institutionCode])) {
            
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            if ($fs->exists($filePath)) {
                $fileContent=  json_decode(file_get_contents($filePath),true);
                $specimensCode = $fileContent['specimensCode'];
                $diffs = $fileContent['diffs'];
                $stats = $fileContent['stats'];
            }
            else {
                /* @var $diffManager \AppBundle\Manager\DiffManager */
                $diffManager = $this->get('diff.manager');
                $diffs[$institutionCode] = $diffManager->getAllDiff($institutionCode);
                $specimensCode = \AppBundle\Manager\DiffManager::getSpecimensCode($diffs[$institutionCode]);
                /* @var $diffStatsManager \AppBundle\Manager\DiffStatsManager */
                $diffStatsManager = $this->get('diff.stats')->init($diffs[$institutionCode]);
                $stats = $diffStatsManager->getStats();
                $responseJson = json_encode(
                        [
                        'specimensCode' => array_values($specimensCode),
                        'stats' => $stats, 
                        'diffs' => $diffs
                        ]
                        , JSON_PRETTY_PRINT);
                $fs->dumpFile($filePath, $responseJson);
            }

            $session->set('diffs', $diffs);
            $session->set('specimensCode', $specimensCode);
            $session->set('stats', $stats);
        } 
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
        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode);
        $exportManager->setChoices($choices);
        $exportManager->saveChoices();

        return new Response(json_encode($exportManager->getChoices()));
    }
    /**
     * @Route("/setChoices/", name="setChoices", options={"expose"=true})
     * @param Request $request
     */
    public function setChoicesAction(Request $request)
    {

        $selectLevel1 = $request->get('selectLevel1', null);
        $selectLevel2 = $request->get('selectLevel2', null);
        $selectLevel3 = $request->get('selectLevel3', null);
        $page = $request->get('page', null);
        $maxItemPerPage = $request->get('maxItemPerPage', self::MAX_SPECIMEN_PAGE);
        $institutionCode = $request->get('institutionCode', null);
        $selectedSpecimens = json_decode($request->get('selectedSpecimens', null));
        dump($request->get('selectedSpecimens', null));
        $choices = [];
        $items = [];
        
        if (!is_null($selectLevel3) && in_array('allClasses', $selectLevel3)) {
            $selectLevel3 = null ;
        }
        if (!is_null($institutionCode) && !is_null($selectLevel1) && !is_null($selectLevel2)) {
            list($specimensCode, $diffs, $stats) = $this->getSpecimenIdsAndDiffsAndStats($request, $institutionCode);
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
                    dump($selectedSpecimens);
                    if (!is_null($selectedSpecimens)) {
                        foreach ($selectedSpecimens as $specimenCode) {
                            if (isset($stats['summary'][$specimenCode]))  {
                                $items[$specimenCode] = $stats['summary'][$specimenCode] ;
                            }
                        }
                    }
                    break;
            }
            /*
            if ($selectLevel2 == 'page' && !is_null($page)) {
                $paginator = $this->get('knp_paginator');
                $pagination = $paginator->paginate(
                        $stats['summary'], $page, $maxItemPerPage
                );
                $items = $pagination->getItems();
            }
            else {
                $items = $stats['summary'] ;
            }*/
            if (count($items) > 0) {
                foreach ($items as $specimenCode=>$row) {
                    foreach ($row as $className => $data) {
                        $rowClass = $stats['classes'][$className][$specimenCode] ;
                        foreach ($rowClass as $relationId => $rowFields) {
                            foreach($rowFields as $fieldName=>$dataFields) {
                                $flag = true ;
                                if (!is_null($selectLevel3) && !in_array(strtolower($className), $selectLevel3)) {
                                    $flag = false ;
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

        return new Response(json_encode($exportManager->getChoices()));
    }

    /**
     * @Route("/export/{institutionCode}", name="export")
     */
    public function exportAction(Request $request, $institutionCode)
    {
        list($specimensCode, $diffs, $stats) = $this->getSpecimenIdsAndDiffsAndStats($request, $institutionCode);
        $responseJson = json_encode(
                [
                'specimensCode' => array_values($specimensCode),
                'data' => $stats['classes']
                ]
                , JSON_PRETTY_PRINT);

        $filePath = realpath($this->getParameter('export_path')) . '/' . $institutionCode . '.json';
        $converterPath = realpath($this->getParameter('converter_path')) ;
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->dumpFile($filePath, $responseJson);
        exec(sprintf('/bin/sh %s --context_param global_filename_json=%s.json',$converterPath,$institutionCode));
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
