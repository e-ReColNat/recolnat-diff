<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontController extends Controller
{
    /**
     * @Route("/", name="default")
     */
    public function defaultAction() 
    {
        return $this->redirectToRoute('index') ;
    }
    
    /**
     * @Route("/files", name="index")
     */
    public function indexAction(Request $request) 
    {
        $institutionCode = 'MHNAIX';
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode);
        $files = $exportManager->getFiles() ;
        return $this->render('default/index.html.twig', array(
            'institutionCode' => $institutionCode,
            'files' => $files,
        ));
    }
    
    /**
     * @Route("{institutionCode}/{filename}/view", name="viewfile")
     */
    public function viewFileAction(Request $request, $filename, $institutionCode) 
    {
        if (!is_null($request->query->get('reset', null))) {
            $this->get('session')->clear();
        }
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $filename);

        list($specimensCode, $diffs, $stats) = $exportManager->getSpecimenIdsAndDiffsAndStats($request);
        
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
        
        return $this->render('default/viewFile.html.twig', array(
            'diffHandler'=>$exportManager->getDiffHandler(),
            'institutionCode' => $institutionCode,
            'filename' => $filename,
            'stats' => $sortedStats,
            'diffs' => $diffs,
            'totalChoices' => $totalChoices,
            'total'=> $total,
        ));
    }
    
    /**
    * @Route("{institutionCode}/{filename}/diffs/{selectedClassName}/{page}", name="diffs", 
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"})
    * @Route("{institutionCode}/{filename}/choices/{selectedClassName}/{page}", name="choices", 
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"})
    * @Route("{institutionCode}/{filename}/todo/{selectedClassName}/{page}", name="todo", 
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"})
    */
    public function diffsAction(Request $request, $institutionCode, $filename, $selectedClassName = "all", $page = 1)
    {
        //if ($selectedClassName == 'all') {$selectedClassName=[];}
        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->get('session') ;
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $filename);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);
        
        list($specimensWithChoices,$specimensWithoutChoices)=[[],[]];
        if ($request->get('_route') == 'choices') {
            $specimensWithChoices=array_keys($exportManager->getChoicesBySpecimenId()) ;
        }
        if ($request->get('_route') == 'todo') {
            $specimensWithoutChoices=$exportManager->getChoices() ;
        }
        
        list($specimensCode, $diffs, $stats) = $exportManager->getSpecimenIdsAndDiffsAndStats($request, $selectedClassName, $specimensWithChoices, $specimensWithoutChoices);

        $specimensRecolnat = $this->getDoctrine() ->getRepository('AppBundle\Entity\Specimen')->findBySpecimenCodes($specimensCode);
        $specimensInstitution = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen', 'diff')->findBySpecimenCodes($specimensCode);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($stats['summary'], $page, $maxItemPerPage);

        return $this->render('default/viewDiffs.html.twig', array(
            'institutionCode' => $institutionCode,
            'filename'=> $filename,
            'stats' => $stats,
            'diffs' => $diffs,
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
