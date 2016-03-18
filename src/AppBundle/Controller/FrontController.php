<?php

namespace AppBundle\Controller;

use AppBundle\Business\DiffHandler;
use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Business\User\User;
use AppBundle\Entity\Collection;
use AppBundle\Form\Type\ExportPrefsType;
use AppBundle\Manager\RecolnatServer;
use Doctrine\ORM\AbstractQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Stopwatch\Stopwatch;

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
        /* @var $institution \AppBundle\Entity\Institution */
        $institution = $this->getDoctrine()->getRepository('AppBundle\Entity\Institution')
            ->findOneBy(['institutioncode' => $institutionCode]);

        $collections=[];
        $diffHandler = new DiffHandler($this->getParameter('export_path').'/'.$institutionCode);
        $exportManager = $this->get('exportManager');
        /** @var Collection $collection */
        foreach ($institution->getCollections() as $collection) {
            $collectionCode = $collection->getCollectioncode() ;
            $collections[$collectionCode]['collection'] = $collection;
            $diffHandler->setCollectionCode($collectionCode);
            $collections[$collectionCode]['diffHandler'] = [];
            if (!$diffHandler->shouldSearchDiffs()) {
                /* @var $exportManager \AppBundle\Manager\ExportManager */
                $exportManager = $exportManager->init($institutionCode, $collectionCode);
                $collections[$collectionCode]['diffHandler'] = $exportManager->getFiles();
            }
        }

        return $this->render('@App/Front/index.html.twig', array(
            'institution' => $institution,
            'collections' => $collections,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/stats", name="stats")
     * @param string $collectionCode
     * @param string $institutionCode
     * @return Response
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
     * @param Request $request
     * @param string  $collectionCode
     * @param string  $institutionCode
     * @return Response
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
            return ($a['diffs'] > $b['diffs']) ? -1 : 1;
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
            'sumLonesomeRecords' => $sumLonesomeRecords,
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
     * @param string  $institutionCode
     * @param string  $collectionCode
     * @param string  $selectedClassName
     * @param int     $page
     * @return Response
     */
    public function diffsAction(
        Request $request,
        $institutionCode,
        $collectionCode,
        $selectedClassName = 'all',
        $page = 1
    ) {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);

        list($specimensWithChoices, $specimensWithoutChoices) = [[], []];
        if ($request->get('_route') == 'choices') {
            $specimensWithChoices = array_keys($exportManager->getSessionHandler()->getChoicesBySpecimenCode());
        }
        if ($request->get('_route') == 'todos') {
            $specimensWithoutChoices = $exportManager->sessionHandler->getChoices();
        }

        $diffs = $exportManager->getDiffs($request, $selectedClassName, $specimensWithChoices,
            $specimensWithoutChoices);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($diffs['datas'], $page, $maxItemPerPage);
        $specimensCode = array_keys($pagination->getItems());

        $specimensRecolnat = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findBySpecimenCodes($specimensCode,
            AbstractQuery::HYDRATE_OBJECT);
        $specimensInstitution = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
            'diff')->findBySpecimenCodes($specimensCode, AbstractQuery::HYDRATE_OBJECT);

        return $this->render('@App/Front/viewDiffs.html.twig', array(
            'institutionCode' => $institutionCode,
            'collectionCode' => $collectionCode,
            'diffs' => $diffs,
            'specimensRecolnat' => $specimensRecolnat,
            'specimensInstitution' => $specimensInstitution,
            'pagination' => $pagination,
            'choicesFacets' => $exportManager->getSessionHandler()->getChoices(),
            'choices' => $exportManager->getSessionHandler()->getChoicesForDisplay(),
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
     * @param string  $institutionCode
     * @param string  $collectionCode
     * @param string  $selectedClassName
     * @param int     $page
     * @param string  $db
     * @return Response
     */
    public function viewLoneSomeAction(
        Request $request,
        $institutionCode,
        $collectionCode,
        $db,
        $selectedClassName = 'all',
        $page = 1
    ) {
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportManager')->init($institutionCode, $collectionCode);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);

        $lonesomesSpecimensBySpecimenCodes = $exportManager->getDiffHandler()->getLonesomeRecordsIndexedBySpecimenCode($db,
            $selectedClassName);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($lonesomesSpecimensBySpecimenCodes, $page, $maxItemPerPage);
        $specimensCode = array_keys($pagination->getItems());


        if ($db == 'recolnat') {
            $specimens = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findBySpecimenCodes($specimensCode,
                AbstractQuery::HYDRATE_OBJECT);
        } else {
            $specimens = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
                'diff')->findBySpecimenCodes($specimensCode, AbstractQuery::HYDRATE_OBJECT);
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
     * @Route("{institutionCode}/{collectionCode}/specimens/view/{jsonSpecimensCode}", name="viewSpecimens",
     *                                                                                 options={"expose"=true})
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

        $specimensRecolnat = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findBySpecimenCodes($specimensCode,
            AbstractQuery::HYDRATE_OBJECT);
        $specimensInstitution = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
            'diff')->findBySpecimenCodes($specimensCode, AbstractQuery::HYDRATE_OBJECT);

        return $this->render('@App/Front/viewSpecimens.html.twig', array(
            'institutionCode' => $institutionCode,
            'collectionCode' => $collectionCode,
            'diffs' => $diffs,
            'specimensRecolnat' => $specimensRecolnat,
            'specimensInstitution' => $specimensInstitution,
            'choicesFacets' => $exportManager->getSessionHandler()->getChoices(),
            'choices' => $exportManager->getSessionHandler()->getChoicesForDisplay(),
            'specimensCode' => $specimensCode,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/specimen/tab/{specimenCode}/{type}/{db}",
     *     requirements={"page": "\d+", "db"="recolnat|institution"}, name="tabSpecimen", options={"expose"=true})
     * @param string $specimenCode
     * @param string $type
     * @param string $db
     * @return Response
     */
    public function viewSpecimenTabAction($specimenCode, $type, $db)
    {
        if ($db == 'recolnat') {
            $specimen = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findOneBySpecimenCode($specimenCode);
        } else {
            $specimen = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
                'diff')->findOneBySpecimenCode($specimenCode);
        }

        $template = 'tab-'.strtolower($type).'.html.twig';

        return $this->render('@App/Front/partial/specimen/'.$template, array(
            'specimen' => $specimen,
            'specimenCode' => $specimenCode,
        ));
    }


    /**
     * @Route("{institutionCode}/{collectionCode}/export/setPrefs/{type}", name="setPrefsForExport",
     *     requirements={"type"="dwc|csv"})
     * @param Request $request
     * @param string  $institutionCode
     * @param string  $collectionCode
     * @param string  $type
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function setPrefsForExportAction(Request $request, $institutionCode, $collectionCode, $type)
    {
        $statsManager = $this->get('statsManager')->init($institutionCode, $collectionCode);

        $exportPrefs = new ExportPrefs();

        $form = $this->createForm(ExportPrefsType::class, $exportPrefs, [
            'action' => $this->generateUrl('setPrefsForExport', [
                'institutionCode' => $institutionCode,
                'collectionCode' => $collectionCode,
                'type' => $type
            ])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paramsExport = [
                'institutionCode' => $institutionCode,
                'collectionCode' => $collectionCode,
                'exportPrefs' => serialize($exportPrefs)
            ];
            switch ($type) {
                case 'dwc':
                    return $this->redirectToRoute('export', array_merge($paramsExport, ['type' => 'dwc']));
                case 'csv':
                    return $this->redirectToRoute('export', array_merge($paramsExport, ['type' => 'csv']));
            }
        }
        $sumStats = $statsManager->getSumStats();
        $statsChoices = $statsManager->getStatsChoices();
        $sumLonesomeRecords = $statsManager->getSumLonesomeRecords();


        return $this->render('@App/Front/setPrefsForExport.html.twig', array(
            'institutionCode' => $institutionCode,
            'collectionCode' => $collectionCode,
            'sumStats' => $sumStats,
            'statsChoices' => $statsChoices,
            'sumLonesomeRecords' => $sumLonesomeRecords,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/generateDiff/{collectionCode}/{compt}", name="generateDiff")
     */
    public function generateDiffAction($collectionCode, $compt)
    {
        $collection = $this->getDoctrine()->getManager()
            ->getRepository('AppBundle:Collection')->findOneBy(['collectioncode' => $collectionCode]);
        /* @var $diffManager \AppBundle\Manager\DiffManager */
        $diffManager = $this->get('diff.manager');
        $diffManager->generateDiff($collection, $compt, rand(1, 5));
        return $this->render('@App/Front/generateDiff.html.twig');
    }

    /**
     * @Route("/test/", name="test")
     */
    public function testAction()
    {
        $institutionCode = 'MHNAIX';
        $collectionCode = 'AIX';
        $collection = $this->getDoctrine()->getManager()
            ->getRepository('AppBundle:Collection')->findOneBy(['collectioncode' => $collectionCode]);
        /*
                $diffManager = $this->get('diff.manager');
                $diffComputer = $this->get('diff.computer');
                $user = new User($this->getParameter('export_path'), $this->getParameter('maxitemperpage'));
                $user->init($institutionCode);

                $diffHandler = new DiffHandler($user->getDataDirPath(), $collectionCode);

                $diffManager->init($collection, $this->getParameter('export_path'));

                $specimenCodes = [];

                $stopwatch = new Stopwatch();
                foreach ($diffManager::ENTITIES_NAME as $entityName) {

                    //if ($entityName == 'Specimen' || $entityName == 'Recolte') {

                    $stopwatch->start($entityName);
                    $specimenCodes[$entityName] = $diffManager->getDiff($entityName);
                    $event = $stopwatch->stop($entityName);
                    dump('stop search : '.$entityName.' '.number_format($event->getDuration() / 1000, 2).'s');

                    $stopwatch->start($entityName);
                    $diffComputer->setSpecimenCodes($specimenCodes);
                    $diffComputer->computeClassname($entityName);
                    $event = $stopwatch->stop($entityName);
                    dump('stop compute : '.$entityName.' '.number_format($event->getDuration() / 1000, 2).'s');
                    //}
                }
        */
        return $this->render('@App/Front/test.html.twig', [
            'collectionCode' => $collectionCode,
        ]);
    }

    /**
     * @Route("/testEvent/{collectionCode}/", name="testEvent", options={"expose"=true})
     */
    public function testEventAction($collectionCode)
    {
        $collection = $this->getDoctrine()->getManager()
            ->getRepository('AppBundle:Collection')->findOneBy(['collectioncode' => $collectionCode]);
        $institutionCode = $collection->getInstitution()->getInstitutioncode();

        $diffManager = $this->get('diff.manager');
        $diffComputer = $this->get('diff.computer');
        $user = new User($this->getParameter('export_path'), $this->getParameter('maxitemperpage'));
        $user->init($institutionCode);

        $diffHandler = new DiffHandler($user->getDataDirPath());

        $diffManager->init($collection, $this->getParameter('export_path'));
        $response = new StreamedResponse();

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        ini_set('output_buffering', 0);
        ob_implicit_flush();

        $response->setCallback(function() use ($diffManager, $diffComputer, $diffHandler) {
            $server = new RecolnatServer();
            $stopwatch = new Stopwatch();
            $specimenCodes = [];

            foreach ($diffManager::ENTITIES_NAME as $entityName) {

                //if ($entityName == 'Specimen' || $entityName == 'Recolte') {
                $server->entity->send('start search : '.$entityName);

                $stopwatch->start($entityName);
                $specimenCodes[$entityName] = $diffManager->getDiff($entityName);
                $event = $stopwatch->stop($entityName);

                $server->entity->send('stop search : '.$entityName.' '.number_format($event->getDuration() / 1000,
                        2).'s');

                $server->entity->send('start compute : '.$entityName);

                $stopwatch->start($entityName);
                $diffComputer->setSpecimenCodes($specimenCodes);
                $diffComputer->computeClassname($entityName);
                $event = $stopwatch->stop($entityName);

                $server->entity->send('stop compute : '.$entityName.' '.number_format($event->getDuration() / 1000,
                        2).'s');
                //}
            }

            $server->entity->send('save');
            $stopwatch->start('save');
            $datas = array_merge($diffComputer->getDiffs(),
                [
                    'stats' => $diffComputer->getAllStats(),
                    'lonesomeRecords' => $diffComputer->getLonesomeRecords(),
                    'statsLonesomeRecords' => $diffComputer->getStatsLonesomeRecords()
                ]);

            //$diffHandler->save($datas);
            $event = $stopwatch->stop('save');
            $server->entity->send('stop save : '.number_format($event->getDuration() / 1000,
                    2).'s');
            $server->close->send(true);
        });

        $response->send();
    }
}
/**
 * $data = array_merge($diffComputer->getDiffs(),
 * [
 * 'stats' => $diffComputer->getAllStats(),
 * 'lonesomeRecords' => $diffComputer->getLonesomeRecords(),
 * 'statsLonesomeRecords' => $diffComputer->getStatsLonesomeRecords()
 * ]);
 * $this->getDiffHandler()->saveDiffs($data);
 */