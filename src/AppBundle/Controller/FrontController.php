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
    public function indexAction()
    {
        $institutionCode = 'MHNAIX';
        $collectionCode = 'AIX';
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $files = $exportManager->getFiles();
        /* @var $institution \AppBundle\Entity\Institution */
        $institution = $this->getDoctrine()->getRepository('AppBundle\Entity\Institution')
            ->findOneBy(['institutioncode' => $institutionCode]);
        return $this->render('@App/Front/index.html.twig', array(
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
        /* @var $institution \AppBundle\Entity\Institution */
        $institution = $this->getDoctrine()->getRepository('AppBundle\Entity\Institution')
            ->findOneBy(['institutioncode' => $institutionCode]);

        /* @var $user \AppBundle\Business\User\User */
        $user = $this->get('userManager');
        $user->init($institutionCode);
        $prefs = $user->getPrefs();

        $statsManager = $this->get('statsManager')->init($institutionCode, $collectionCode);

        $statsBySimilarity = $statsManager->getStatsBySimilarity([], $prefs->getCsvDateFormat());
        $sumStats = $statsManager->getSumStats();
        return $this->render('@App/Front/stats.html.twig', array(
            'institutionCode' => $institutionCode,
            'collectionCode' => $collectionCode,
            'stats' => $statsBySimilarity,
            'institution' => $institution,
            'sumStats' => $sumStats,
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

        $statsManager = $this->get('statsManager')->init($institutionCode, $collectionCode);

        $stats = $statsManager->getExpandedStats();
        $sumStats = $statsManager->getSumStats();
        $statsLonesomeRecords = $statsManager->getStatsLonesomeRecords();
        $sortStats = function($a, $b) {
            if ($a['diffs'] == $b['diffs']) {
                return 0;
            }
            return ($a['diffs']>$b['diffs']) ? -1 : 1;
        };
        uasort($stats, $sortStats);

        $statsChoices = $statsManager->getStatsChoices();
        $sumLonesomeRecords = $statsManager->getSumLonesomeRecords();

        return $this->render('@App/Front/viewFile.html.twig', array(
            'diffHandler' => $exportManager->getDiffHandler(),
            'institutionCode' => $institutionCode,
            'collectionCode' => $collectionCode,
            'stats' => $stats,
            'sumStats' => $sumStats,
            'statsChoices' => $statsChoices,
            'institution' => $institution,
            'statsLonesomeRecords' => $statsLonesomeRecords,
            'sumLonesomeRecords'=>$sumLonesomeRecords,
        ));
    }


    /**
     * @Route("{institutionCode}/{collectionCode}/diffs/{selectedClassName}/{page}", name="diffs",
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"}, options={"expose"=true})
     * @Route("{institutionCode}/{collectionCode}/choices/{selectedClassName}/{page}", name="choices",
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"}, options={"expose"=true})
     * @Route("{institutionCode}/{collectionCode}/todo/{selectedClassName}/{page}", name="todos",
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"}, options={"expose"=true})
     *
     * @param Request $request
     * @param string $institutionCode
     * @param string $collectionCode
     * @param string $selectedClassName
     * @param int $page
     * @return Response
     */
    public function diffsAction(Request $request, $institutionCode, $collectionCode, $selectedClassName = "all", $page = 1)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);

        list($specimensWithChoices, $specimensWithoutChoices) = [[], []];
        if ($request->get('_route') == 'choices') {
            $specimensWithChoices = array_keys($exportManager->getChoicesBySpecimenCode());
        }
        if ($request->get('_route') == 'todos') {
            $specimensWithoutChoices = $exportManager->getChoices();
        }

        $diffs = $exportManager->getDiffs($request, $selectedClassName, $specimensWithChoices, $specimensWithoutChoices);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($diffs['datas'], $page, $maxItemPerPage);
        $specimensCode = array_keys($pagination->getItems());

        $specimensRecolnat = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findBySpecimenCodes($specimensCode);
        $specimensInstitution = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen', 'diff')->findBySpecimenCodes($specimensCode);

        return $this->render('@App/Front/viewDiffs.html.twig', array(
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
     * @Route("{institutionCode}/{collectionCode}/lonesomes/{db}/{selectedClassName}/{page}", name="lonesomes",
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+", "db"="recolnat|institution"},
     * options={"expose"=true})
     *
     * @param Request $request
     * @param string $institutionCode
     * @param string $collectionCode
     * @param string $selectedClassName
     * @param int $page
     * @return Response
     */
    public function viewLoneSomeAction(Request $request, $institutionCode, $collectionCode, $selectedClassName = "all", $page = 1, $db)
    {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);

        $lonesomesSpecimensBySpecimenCodes=$exportManager->getLonesomeRecordsBySpecimenCode($db, $selectedClassName);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($lonesomesSpecimensBySpecimenCodes, $page, $maxItemPerPage);
        $specimensCode = array_keys($pagination->getItems());


        if ($db=='recolnat') {
            $specimens=$this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findBySpecimenCodes($specimensCode);
        } else {
            $specimens=$this->getDoctrine()->getRepository('AppBundle\Entity\Specimen', 'diff')->findBySpecimenCodes($specimensCode);
        }

        return $this->render('@App/Front/viewLonesome.html.twig', array(
            'institutionCode' => $institutionCode,
            'collectionCode' => $collectionCode,
            'specimens' => $specimens,
            'pagination' => $pagination,
            'maxItemPerPage' => $maxItemPerPage,
            'selectedClassName' => $selectedClassName,
            'db' => $db,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/specimens/view/{jsonSpecimensCode}", name="viewSpecimens", options={"expose"=true})
     * @param string $institutionCode
     * @param string $collectionCode
     * @param string $jsonSpecimensCode
     * @return Response
     */
    public function viewSpecimensAction($institutionCode, $collectionCode, $jsonSpecimensCode)
    {
        $specimensCode = json_decode($jsonSpecimensCode);
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $diffs = $exportManager->getDiffsBySpecimensCode($specimensCode);

        $specimensRecolnat = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findBySpecimenCodes($specimensCode);
        $specimensInstitution = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen', 'diff')->findBySpecimenCodes($specimensCode);

        return $this->render('@App/Front/viewSpecimens.html.twig', array(
            'institutionCode' => $institutionCode,
            'collectionCode' => $collectionCode,
            'diffs' => $diffs,
            'specimensRecolnat' => $specimensRecolnat,
            'specimensInstitution' => $specimensInstitution,
            'choicesFacets' => $exportManager->getChoices(),
            'choices' => $exportManager->getChoicesForDisplay(),
            'specimensCode' => $specimensCode,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/specimen/tab/{specimenCode}/{type}/{db}",
     *     requirements={"page": "\d+", "db"="recolnat|institution"}, name="tabSpecimen", options={"expose"=true})
     */
    public function viewSpecimenTabAction($specimenCode, $type, $db)
    {
        if ($db == 'recolnat') {
            $specimen = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findOneBySpecimenCode($specimenCode);
        } else {
            $specimen = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen', 'diff')->findOneBySpecimenCode($specimenCode);
        }

        $template = 'tab-'.strtolower($type).'.html.twig';

        return $this->render('@App/Front/partial/specimen/'.$template, array(
            'specimen' => $specimen,
            'specimenCode' => $specimenCode,
        ));
    }


    /**
     * @Route("{institutionCode}/{collectionCode}/export/setPrefs", name="setPrefsForExport")
     * @param string $institutionCode
     * @param string $collectionCode
     * @return Response
     */
    public function setPrefsForExportAction($institutionCode, $collectionCode)
    {
        $statsManager = $this->get('statsManager')->init($institutionCode, $collectionCode);

        $sumStats = $statsManager->getSumStats();
        $statsChoices = $statsManager->getStatsChoices();
        $sumLonesomeRecords = $statsManager->getSumLonesomeRecords();
        dump($sumLonesomeRecords);

        return $this->render('@App/Front/setPrefsForExport.html.twig', array(
            'institutionCode' => $institutionCode,
            'collectionCode' => $collectionCode,
            'sumStats' => $sumStats,
            'statsChoices' => $statsChoices,
            'sumLonesomeRecords'=>$sumLonesomeRecords,
        ));
    }

    /**
     * @Route("/generateDiff/{institutionCode}/{compt}", name="generateDiff")
     */
//    public function generateDiff($compt)
//    {
//        /* @var $diffManager \AppBundle\Manager\DiffManager */
//        $em = $this->get('doctrine')->getManager('diff');
//        $diffManager = new \AppBundle\Manager\DiffManager($em);
//        //$diffManager = $this->get('diff.manager');
//        for ($i = 1; $i <= $compt; $i++) {
//            $diffManager->generateDiff(rand(1, 5));
//        }
//        $response = new Response();
//        return $response;
//    }

}
