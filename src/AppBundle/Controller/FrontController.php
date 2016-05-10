<?php

namespace AppBundle\Controller;

use AppBundle\Business\DiffHandler;
use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Entity\Collection;
use AppBundle\Form\Type\ExportPrefsType;
use AppBundle\Manager\ExportManager;
use Doctrine\ORM\AbstractQuery;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class FrontController extends Controller
{

    /**
     * @Route("/", name="index")
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        /* @var $institution \AppBundle\Entity\Institution */
        $institution = $this->getUser()->getInstitution();

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
     * @Route("{collectionCode}/stats", name="stats")
     * @param string $collectionCode
     * @return Response
     */
    public function statsAction($collectionCode)
    {
        $prefs = $this->getUser()->getPrefs();

        $statsManager = $this->get('statsmanager')->init($this->getUser(), $collectionCode);

        $statsBySimilarity = $statsManager->getStatsBySimilarity([], $prefs->getCsvDateFormat());
        $sumStats = $statsManager->getSumStats();

        return $this->render('@App/Front/stats.html.twig', array(
            'collectionCode' => $collectionCode,
            'stats' => $statsBySimilarity,
            'sumStats' => $sumStats,
        ));
    }

    /**
     * @Route("{collectionCode}/view", name="viewfile")
     * @param Request $request
     * @param string  $collectionCode
     * @return Response
     */
    public function viewFileAction(Request $request, $collectionCode)
    {
        if (!is_null($request->query->get('reset', null))) {
            $this->get('session')->clear();
        }

        $collection = $this->getCollection($collectionCode);

        $statsManager = $this->get('statsmanager')->init($this->getUser(), $collectionCode);


        return $this->render('@App/Front/viewFile.html.twig', array(
            'statsManager' => $statsManager,
            'collection' => $collection,
        ));
    }


    /**
     * @Route("{collectionCode}/diffs/{selectedClassName}/{page}", name="diffs",
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"}, options={"expose"=true})
     * @Route("{collectionCode}/choices/{selectedClassName}/{page}", name="choices",
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"}, options={"expose"=true})
     * @Route("{collectionCode}/todo/{selectedClassName}/{page}", name="todos",
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+"}, options={"expose"=true})
     *
     * @param Request $request
     * @param string  $collectionCode
     * @param string  $selectedClassName
     * @param int     $page
     * @return Response
     */
    public function viewDiffsAction(Request $request, $collectionCode, $selectedClassName = 'all', $page = 1)
    {
        $collection = $this->getCollection($collectionCode);
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);

        list($specimensWithChoices, $specimensWithoutChoices) = [[], []];
        if ($request->get('_route') == 'choices') {
            $specimensWithChoices = array_keys($exportManager->getSessionHandler()->getChoicesByCatalogNumber());
        }
        if ($request->get('_route') == 'todos') {
            $specimensWithoutChoices = $exportManager->sessionHandler->getChoices();
        }

        $diffs = $exportManager->getDiffs($request, $selectedClassName, $specimensWithChoices,
            $specimensWithoutChoices);

        $paginator = $this->get('knp_paginator');
        /** @var AbstractPagination $pagination */
        $pagination = $paginator->paginate($diffs['datas'], $page, $maxItemPerPage);
        $catalogNumbers = array_keys($pagination->getItems());
        $specimens = [];
        $specimens['recolnat'] = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findByCatalogNumbers($collection,
            $catalogNumbers, AbstractQuery::HYDRATE_OBJECT);
        $specimens['institution'] = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
            'diff')->findByCatalogNumbers($collection, $catalogNumbers, AbstractQuery::HYDRATE_OBJECT);

        return $this->render('@App/Front/viewDiffs.html.twig', array(
            'collection' => $collection,
            'diffs' => $diffs,
            'specimens' => $specimens,
            'pagination' => $pagination,
            'exportManager' => $exportManager,
        ));
    }

    /**
     * @Route("{collectionCode}/lonesomes/{db}/{selectedClassName}/{page}", name="lonesomes",
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+", "db"="recolnat|institution"},
     * options={"expose"=true})
     *
     * @param Request $request
     * @param string  $collectionCode
     * @param string  $selectedClassName
     * @param int     $page
     * @param string  $db
     * @return Response
     */
    public function viewLonesomesAction(Request $request, $collectionCode, $db, $selectedClassName = 'all', $page = 1)
    {
        $collection = $this->getCollection($collectionCode);

        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);

        $lonesomeRecords = $exportManager->getDiffHandler()
            ->getLonesomeRecordsIndexedByCatalogNumber($db, $selectedClassName);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($lonesomeRecords, $page, $maxItemPerPage);
        $catalogNumbers = array_keys($pagination->getItems());

        if ($db == 'recolnat') {
            $specimens = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findByCatalogNumbers($collection,
                $catalogNumbers, AbstractQuery::HYDRATE_OBJECT);
        } else {
            $specimens = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
                'diff')->findByCatalogNumbers($collection, $catalogNumbers, AbstractQuery::HYDRATE_OBJECT);
        }

        return $this->render('@App/Front/viewLonesome.html.twig', array(
            'collection' => $collection,
            'specimens' => $specimens,
            'pagination' => $pagination,
            'db' => $db,
            'exportManager' => $exportManager,
            'lonesomeRecords' => $lonesomeRecords,
        ));
    }

    /**
     * @Route("{collectionCode}/specimen/tab/{catalogNumber}/{type}/{db}",
     *     requirements={"page": "\d+", "db"="recolnat|institution"}, name="tabSpecimen", options={"expose"=true})
     * @ParamConverter("collection", options={"mapping": {"collectionCode": "collectioncode"}})
     * @param string $catalogNumber
     * @param string $type
     * @param string $db
     * @return Response
     */
    public function viewSpecimenTabAction(Collection $collection, $catalogNumber, $type, $db)
    {
        if ($db == 'recolnat') {
            $specimen = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')
                ->findOneByCatalogNumber($collection, $catalogNumber);
        } else {
            $specimen = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
                'diff')->findOneByCatalogNumber($collection, $catalogNumber);
        }

        $template = 'tab-'.strtolower($type).'.html.twig';

        return $this->render('@App/Front/partial/specimen/'.$template, array(
            'specimen' => $specimen,
            'catalogNumber' => $catalogNumber,
        ));
    }


    /**
     * @Route("{collectionCode}/export/setPrefs/{type}", name="setPrefsForExport",
     *     requirements={"type"="dwc|csv"})
     * @param Request $request
     * @param string  $collectionCode
     * @param string  $type
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function setPrefsForExportAction(Request $request, $collectionCode, $type)
    {
        $institutionCode = $this->getUser()->getInstitutionCode();
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


    /**
     * @Route("{collectionCode}/specimens/view/{jsonCatalogNumbers}/{page}", name="viewSpecimens",
     * options={"expose"=true}, defaults={"page"= 1}, requirements={"page": "\d+"})
     * @param Request $request
     * @param string  $collectionCode
     * @param string  $jsonCatalogNumbers
     * @param int     $page
     * @return Response
     */
    public function viewSpecimensAction(Request $request, $collectionCode, $jsonCatalogNumbers, $page = 1)
    {
        $collection = $this->getCollection($collectionCode);
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);

        $catalogNumbers = json_decode($jsonCatalogNumbers);

        list($pagination, $diffs, $specimens) = $this->getDataForDisplay($page, $catalogNumbers, $request,
            $exportManager, $collection);

        return $this->render('@App/Front/viewSpecimens.html.twig', array(
            'collection' => $collection,
            'diffs' => $diffs,
            'specimens' => $specimens,
            'exportManager' => $exportManager,
            'pagination' => $pagination,
        ));
    }

    /**
     * @Route("{collectionCode}/search/{page}", name="search", defaults={"page"= 1}, requirements={"page": "\d+"})
     * @param String  $collectionCode
     * @param Integer $page
     * @param Request $request
     * @return Response
     */
    public function searchAction(Request $request, $collectionCode, $page = 1)
    {
        $search = $request->get('search', '');

        if (empty($search)) {
            return $this->redirectToRoute('viewfile', ['collectionCode' => $collectionCode]);
        }
        $collection = $this->getCollection($collectionCode);

        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);

        $catalogNumbers = $exportManager->getDiffHandler()->search($search);

        list($pagination, $diffs, $specimens) = $this->getDataForDisplay($page, $catalogNumbers, $request,
            $exportManager, $collection);

        return $this->render('@App/Front/viewSpecimens.html.twig', array(
            'collection' => $collection,
            'diffs' => $diffs,
            'specimens' => $specimens,
            'exportManager' => $exportManager,
            'search' => $search,
            'pagination' => $pagination,
        ));
    }

    /**
     * @Route("{collectionCode}/list/{type}/{page}", name="list", defaults={"page"= 1}, requirements={"page": "\d+"})
     * @param String $collectionCode
     * @param String $type
     * @return Response
     */
    public function listSpecimensAction($collectionCode, $type)
    {
        $collection = $this->getCollection($collectionCode);
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);

        $specimens = [];
        $orderSpecimens = [];
        $orderSpecimensOuput = [];
        switch ($type) {
            case 'alpha' :
                $specimens = $exportManager->getDiffs()['datas'];
                break;
            case 'selected' :
                $catalogNumbers = $this->get('session')->get('selectedSpecimens');
                $specimens = $exportManager::orderDiffsByTaxon($exportManager->getDiffsByCatalogNumbers($catalogNumbers))['datas'];
                break;
        }
        if (count($specimens)) {
            $withoutTaxon = [];
            foreach ($specimens as $catalogNumber => $specimen) {
                if (!(empty($specimen['taxon']))) {
                    $firstLetter = mb_substr($specimen['taxon'], 0, 1);
                    $letter = mb_strtoupper($firstLetter, 'UTF-8');
                    $orderSpecimens[$letter][$catalogNumber] = $specimen;
                } else {
                    $withoutTaxon[] = $specimen;
                }
                if (count($withoutTaxon)) {
                    $orderSpecimensOuput = ['N/A' => $withoutTaxon] + $orderSpecimens;
                } else {
                    $orderSpecimensOuput = $orderSpecimens;
                }
            }
        }

        return $this->render('@App/Front/list.html.twig', array(
            'collection' => $collection,
            'exportManager' => $exportManager,
            'orderSpecimens' => $orderSpecimensOuput,
            'type' => $type
        ));

    }

    /**
     * @param $collectionCode
     * @return Collection
     */
    private function getCollection($collectionCode)
    {
        $collection = $this->getDoctrine()->getRepository('AppBundle\Entity\Collection')
            ->findOneBy(['collectioncode' => $collectionCode]);

        return $collection;
    }

    /**
     * @param int           $page
     * @param array         $catalogNumbers
     * @param Request       $request
     * @param ExportManager $exportManager
     * @param Collection    $collection
     * @return array
     */
    private function getDataForDisplay($page, $catalogNumbers, $request, $exportManager, $collection)
    {
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($catalogNumbers, $page, $maxItemPerPage);
        $catalogNumbers = $pagination->getItems();

        $diffs = $exportManager->getDiffsByCatalogNumbers($catalogNumbers);
        $specimens = [];

        $specimens['recolnat'] = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')
            ->findByCatalogNumbers($collection, $catalogNumbers, AbstractQuery::HYDRATE_OBJECT);

        $specimens['institution'] = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
            'diff')->findByCatalogNumbers($collection, $catalogNumbers, AbstractQuery::HYDRATE_OBJECT);

        return array($pagination, $diffs, $specimens);
    }
}
