<?php

namespace AppBundle\Controller;

use AppBundle\Business\DiffHandler;
use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Entity\Collection;
use AppBundle\Form\Type\ExportPrefsType;
use Doctrine\ORM\AbstractQuery;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontController extends Controller
{

    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        /* @var $institution \AppBundle\Entity\Institution */
        $institution = $this->getUser()->getInstitution();

        dump($this->getUser());
        $collections = [];
        $diffHandler = new DiffHandler($this->getParameter('export_path').'/'.$institution->getInstitutioncode());
        $exportManager = $this->get('exportmanager')->init($this->getUser());

        /** @var Collection $collection */
        foreach ($institution->getCollections() as $collection) {
            $collectionCode = $collection->getCollectioncode();
            $exportManager->setCollectionCode($collectionCode);
            $collections[$collectionCode]['collection'] = $collection;
            $diffHandler->setCollectionCode($collectionCode);
            $collections[$collectionCode]['diffHandler'] = [];
            if (!$diffHandler->shouldSearchDiffs()) {
                /* @var $exportManager \AppBundle\Manager\ExportManager */
                $collections[$collectionCode]['diffHandler'] = $exportManager->getFiles();
            }
        }

        return $this->render('@App/Front/index.html.twig', array(
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
        $user = $this->getUser();
        $user->init($institutionCode);
        $prefs = $user->getPrefs();

        $statsManager = $this->get('statsmanager')->init($this->getUser(), $collectionCode);

        $statsBySimilarity = $statsManager->getStatsBySimilarity([], $prefs->getCsvDateFormat());
        $sumStats = $statsManager->getSumStats();

        return $this->render('@App/Front/stats.html.twig', array(
            'institutionCode' => $institutionCode,
            'collectionCode' => $collectionCode,
            'stats' => $statsBySimilarity,
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

        $collection = $this->getDoctrine()->getRepository('AppBundle\Entity\Collection')
            ->findOneBy(['collectioncode' => $collectionCode]);

        $statsManager = $this->get('statsmanager')->init($this->getUser(), $collectionCode);


        return $this->render('@App/Front/viewFile.html.twig', array(
            'statsManager' => $statsManager,
            'collection' => $collection,
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
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);

        $collection = $this->getDoctrine()->getRepository('AppBundle\Entity\Collection')
            ->findOneBy(['collectioncode' => $collectionCode]);

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
        /** @var AbstractPagination $pagination */
        $pagination = $paginator->paginate($diffs['datas'], $page, $maxItemPerPage);
        $specimensCode = array_keys($pagination->getItems());

        dump($pagination->getItemNumberPerPage());

        $specimensRecolnat = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findBySpecimenCodes($specimensCode,
            AbstractQuery::HYDRATE_OBJECT);
        $specimensInstitution = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
            'diff')->findBySpecimenCodes($specimensCode, AbstractQuery::HYDRATE_OBJECT);

        return $this->render('@App/Front/viewDiffs.html.twig', array(
            'collection' => $collection,
            'diffs' => $diffs,
            'specimensRecolnat' => $specimensRecolnat,
            'specimensInstitution' => $specimensInstitution,
            'pagination' => $pagination,
            'exportManager' => $exportManager,
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
        $collection = $this->getDoctrine()->getRepository('AppBundle\Entity\Collection')
            ->findOneBy(['collectioncode' => $collectionCode]);

        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);
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
            'collection' => $collection,
            'specimens' => $specimens,
            'pagination' => $pagination,
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
        $collection = $this->getDoctrine()->getRepository('AppBundle\Entity\Collection')
            ->findOneBy(['collectioncode' => $collectionCode]);

        $specimensCode = json_decode($jsonSpecimensCode);
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);
        $diffs = $exportManager->getDiffsBySpecimensCode($specimensCode);

        $specimensRecolnat = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findBySpecimenCodes($specimensCode,
            AbstractQuery::HYDRATE_OBJECT);
        $specimensInstitution = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
            'diff')->findBySpecimenCodes($specimensCode, AbstractQuery::HYDRATE_OBJECT);

        return $this->render('@App/Front/viewSpecimens.html.twig', array(
            'collection' => $collection,
            'diffs' => $diffs,
            'specimensRecolnat' => $specimensRecolnat,
            'specimensInstitution' => $specimensInstitution,
            'specimensCode' => $specimensCode,
            'exportManager' => $exportManager,
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
        $statsManager = $this->get('statsmanager')->init($this->getUser(), $collectionCode);

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
     * @param string  $collectionCode
     * @param integer $compt
     * @return Response
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
}
