<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request) {
        $institutionCode = 'MHNAIX';
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode);
        $files = $exportManager->getFiles() ;
        var_dump($files) ;
        return $this->render('default/index.html.twig', array(
            'institutionCode' => $institutionCode,
            'files' => $files,
        ));
    }
    /**
     * @Route("/file/{filename}", name="viewfile")
     */
    public function viewFileAction(Request $request, $filename) {
        $institutionCode = 'MHNAIX';
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $filename);

        /* @var $specimenService \AppBundle\Services\ServiceSpecimen */
        $specimenService = $this->get('specimenService') ;
        list($specimensCode, $diffs, $stats) = $specimenService->getSpecimenIdsAndDiffsAndStats($request, $institutionCode);
        
        $choices=$exportManager->getChoicesForDisplay();
        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->get('session') ;
        dump($session->all()) ;
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
        
        return $this->render('default/viewFile.html.twig', array(
            'institutionCode' => $institutionCode,
            'filename' => $filename,
            'stats' => $sortedStats,
            'diffs' => $diffs[$institutionCode],
            'totalChoices' => $totalChoices,
            'total'=> $total,
        ));
    }
    
    /**
    * @Route("{institutionCode}/{filename}/diffs/{selectedClassName}/{page}", name="diffs", defaults={"selectedClassName" = "all", "page" = 1}, requirements={
    *     "page": "\d+"
    * })
    * @Route("{institutionCode}/{filename}/choices/{selectedClassName}/{page}", name="choices", defaults={"selectedClassName" = "all", "page" = 1}, requirements={
    *     "page": "\d+"
    * })
    * @Route("{institutionCode}/{filename}/todo/{selectedClassName}/{page}", name="todo", defaults={"selectedClassName" = "all", "page" = 1}, requirements={
    *     "page": "\d+"
    * })
    */
    public function diffsAction(Request $request, $institutionCode, $filename, $selectedClassName = "all", $page = 1)
    {
        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->get('session') ;
        dump($session->all()) ;
        /* @var $specimenService \AppBundle\Services\ServiceSpecimen */
        $specimenService = $this->get('specimenService') ;
        $maxItemPerPage = $specimenService->getMaxItemPerPage($request);
        
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $filename);
        
        list($specimensWithChoices,$specimensWithoutChoices)=[[],[]];
        if ($request->get('_route') == 'choices') {
            $specimensWithChoices=array_keys($exportManager->getChoicesBySpecimenId()) ;
        }
        if ($request->get('_route') == 'todo') {
            $specimensWithoutChoices=$exportManager->getChoices() ;
        }
        
        list($specimensCode, $diffs, $stats) = $specimenService->getSpecimenIdsAndDiffsAndStats($request, $institutionCode, $selectedClassName, $specimensWithChoices, $specimensWithoutChoices);

        $specimensRecolnat = $this->getDoctrine() ->getRepository('AppBundle\Entity\Specimen')->findBySpecimenCodes($specimensCode);
        $specimensInstitution = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen', 'diff')->findBySpecimenCodes($specimensCode);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($stats['summary'], $page, $maxItemPerPage);

        return $this->render('default/viewDiffs.html.twig', array(
            'institutionCode' => $institutionCode,
            'filename'=> $filename,
            'stats' => $stats,
            'diffs' => $diffs[$institutionCode],
            'specimensRecolnat' => $specimensRecolnat,
            'specimensInstitution' => $specimensInstitution,
            'pagination' => $pagination,
            'choicesFacets' => $exportManager->getChoices(),
            'choices' => $exportManager->getChoicesForDisplay(),
            'maxItemPerPage'=>$maxItemPerPage,
            'selectedClassName' => $selectedClassName,
            'type'=>$request->get('_route'),
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
//    private function getSpecimenIdsAndDiffsAndStats(Request $request, $institutionCode, $selectedClassName=null, $specimensWithChoices=[], $choicesToRemove=[])
//    {
//        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
//        $session = $this->get('session');
//
//        if ($selectedClassName == "all") {$selectedClassName = null;}
//        if (!is_null($request->query->get('reset', null))) {
//            $session->clear();
//        }
//        /* @var $diffManager \AppBundle\Manager\DiffManager */
//        $diffManager = $this->get('diff.manager');
//        $results =$diffManager->init($institutionCode, [$selectedClassName], $specimensWithChoices, $choicesToRemove);
//        list ($diffs, $specimensCode, $stats) = [$results['diffs'],$results['specimensCode'],$results['stats']] ;
//        $session->set('diffs', $diffs);
//        $session->set('specimensCode', $specimensCode);
//        $session->set('stats', $stats);
//        return [$specimensCode, $diffs, $stats];
//    }


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
