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
        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/files", name="index")
     */
    public function indexAction(Request $request)
    {
        $institutionCode = 'MHNAIX';
        $collectionCode = 'AIX';
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $files = $exportManager->getFiles();
        /* @var $institution \AppBundle\Entity\Institution */
        $institution = $this->getDoctrine()->getRepository('AppBundle\Entity\Institution')
                ->findOneBy(['institutioncode' => $institutionCode]);
        return $this->render('default/index.html.twig', array(
                    'institutionCode' => $institutionCode,
                    'files' => $files,
                    'institution' => $institution,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/stats", name="stats")
     */
    public function statsAction($collectionCode, $institutionCode)
    {
        /* @var $user \AppBundle\Business\User\User */
        $user = $this->get('userManager');
        $user->init($institutionCode);
        $prefs = $user->getPrefs();
        
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $stats = $exportManager->getExpandedStats();
        $diffs = $exportManager->getDiffs();
        $statsBySimilarity = $exportManager->getStatsBySimilarity([], $prefs->getCsvDateFormat());
        dump($diffs);
        dump($statsBySimilarity);
        dump($stats);
        return $this->render('default/stats.html.twig', array(
                    'institutionCode' => $institutionCode,
                    'collectionCode' => $collectionCode,
                    'stats' => $statsBySimilarity,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/view", name="viewfile")
     */
    public function viewFileAction(Request $request, $collectionCode, $institutionCode)
    {
        if (!is_null($request->query->get('reset', null))) {
            $this->get('session')->clear();
        }
        /* @var $institution \AppBundle\Entity\Institution */
        $institution = $this->getDoctrine()->getRepository('AppBundle\Entity\Institution')
                ->findOneBy(['institutioncode' => $institutionCode]);
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);

        $choices = $exportManager->getChoicesForDisplay();
        $stats = $exportManager->getExpandedStats();
        $totalChoices = [];
        $sumStats = $exportManager->getSumStats();


        $callbackCountChoices = function($value, $className) use (&$totalChoices) {
            if (is_array($value)) {
                if (!isset($total[$className])) {
                    $totalChoices[$className] = 0;
                }
                foreach ($value as $row) {
                    foreach ($row as $fields) {
                        $totalChoices[$className] += count($fields);
                    }
                }
            }
        };
        array_walk($choices, $callbackCountChoices);
        $totalDiffs = array_sum($stats);
        $totalChoices['sum'] = array_sum($totalChoices);

        return $this->render('default/viewFile.html.twig', array(
                    'diffHandler' => $exportManager->getDiffHandler(),
                    'institutionCode' => $institutionCode,
                    'collectionCode' => $collectionCode,
                    'stats' => $stats,
                    'sumStats' => $sumStats,
                    'totalChoices' => $totalChoices,
                    'institution' => $institution,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/diffs/{selectedClassName}/{page}", name="diffs", 
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"})
     * @Route("{institutionCode}/{collectionCode}/choices/{selectedClassName}/{page}", name="choices", 
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"})
     * @Route("{institutionCode}/{collectionCode}/todo/{selectedClassName}/{page}", name="todo", 
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"})
     */
    public function diffsAction(Request $request, $institutionCode, $collectionCode, $selectedClassName = "all", $page = 1)
    {
        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->get('session');
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);

        list($specimensWithChoices, $specimensWithoutChoices) = [[], []];
        if ($request->get('_route') == 'choices') {
            $specimensWithChoices = array_keys($exportManager->getChoicesBySpecimenId());
        }
        if ($request->get('_route') == 'todo') {
            $specimensWithoutChoices = $exportManager->getChoices();
        }

        $diffs = $exportManager->getDiffs($request, $selectedClassName, $specimensWithChoices, $specimensWithoutChoices);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($diffs['summary'], $page, $maxItemPerPage);
        $specimensCode = array_keys($pagination->getItems());

        $specimensRecolnat = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findBySpecimenCodes($specimensCode);
        $specimensInstitution = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen', 'diff')->findBySpecimenCodes($specimensCode);

        return $this->render('default/viewDiffs.html.twig', array(
                    'institutionCode' => $institutionCode,
                    'collectionCode' => $collectionCode,
                    'diffs' => $diffs,
                    'specimensRecolnat' => $specimensRecolnat,
                    'specimensInstitution' => $specimensInstitution,
                    'pagination' => $pagination,
                    'choicesFacets' => $exportManager->getChoices(),
                    'choices' => $exportManager->getChoicesForDisplay(),
                    'maxItemPerPage' => $maxItemPerPage,
                    'selectedClassName' => $selectedClassName,
                    'type' => $request->get('_route'),
        ));
    }

    /**
     * @Route("/generateDiff/{institutionCode}/{compt}", name="generateDiff")
     */
    public function generateDiff($compt)
    {
        /* @var $diffManager \AppBundle\Manager\DiffManager */
        $em = $this->get('doctrine')->getManager('diff');
        $diffManager = new \AppBundle\Manager\DiffManager($em);
        //$diffManager = $this->get('diff.manager');
        for ($i = 1; $i <= $compt; $i++) {
            $diffManager->generateDiff(rand(1, 5));
        }
        $response = new Response();
        return $response;
    }

}
