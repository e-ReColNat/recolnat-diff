<?php

namespace AppBundle\Controller;

use AppBundle\Business\User\Prefs;
use AppBundle\Business\User\User;
use AppBundle\Entity\Collection;
use AppBundle\Manager\AbstractDiff;
use AppBundle\Manager\ExportManager;
use Doctrine\ORM\AbstractQuery;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class FrontController extends Controller
{

    /**
     * @Route("/", name="index")
     * @param UserInterface|User $user
     * @return Response
     * @throws \Exception
     */
    public function indexAction(UserInterface $user)
    {
        $exportManager = $this->get('exportmanager')->init($user);

        $managedCollectionsByInstitution = [];
        if ($user->isSuperAdmin()) {
            $managedCollections = $this->getDoctrine()->getManager()
                ->getRepository('AppBundle:Collection')->findAllOrderByInstitution();
        } else {
            $managedCollections = $this->getDoctrine()->getManager()
                ->getRepository('AppBundle:Collection')->findBy(['collectioncode' => $user->getManagedCollections()]);
        }
        if (count($managedCollections)) {
            /** @var Collection $collection */
            foreach ($managedCollections as $collection) {
                $managedCollectionsByInstitution[$collection->getInstitution()->getInstitutioncode()][] = $collection;
            }
        }
        $diffHandlers = $exportManager->getDiffHandlers();

        return $this->render('@App/Front/index.html.twig', array(
            'managedCollectionsByInstitution' => $managedCollectionsByInstitution,
            'diffHandlers' => $diffHandlers,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/stats/{page}", name="stats",defaults={ "page" = 1},
     *     requirements={"page": "\d+"})
     * @param UserInterface|User $user
     * @param string $institutionCode
     * @param string $collectionCode
     * @param int $page
     * @return Response
     */
    public function statsAction(UserInterface $user, $institutionCode, $collectionCode, $page = 1)
    {
        /** @var Prefs $prefs */
        $prefs = $user->getPrefs();

        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $user);

        $statsManager = $this->get('statsmanager')->init($user, $collection);

        list($statsBySimilarity, $catalogNumbers) = $statsManager->getStatsBySimilarity([], $prefs->getCsvDateFormat());
        $sumStats = $statsManager->getSumStats();

        $paginator = $this->get('knp_paginator');
        /** @var AbstractPagination $pagination */
        $pagination = $paginator->paginate($statsBySimilarity, $page, 100);

        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($user)->setCollection($collection);
        $taxons = $exportManager->getDiffHandler()->getTaxons($catalogNumbers);

        return $this->render('@App/Front/stats.html.twig', array(
            'collection' => $collection,
            'sumStats' => $sumStats,
            'pagination' => $pagination,
            'keyRecolnat' => AbstractDiff::KEY_RECOLNAT,
            'keyInstitution' => AbstractDiff::KEY_INSTITUTION,
            'taxons' => $taxons,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/file/view", name="viewfile", options={"expose"=true})
     * @param UserInterface|User $user
     * @param string $institutionCode
     * @param string $collectionCode
     * @return Response
     */
    public function viewFileAction(UserInterface $user, $institutionCode, $collectionCode)
    {
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $user);

        $statsManager = $this->get('statsmanager')->init($user, $collection);
        $statsManager->getSumLonesomeRecords();
        $this->get('exportmanager')->init($user)->setCollection($collection);


        return $this->render('@App/Front/viewFile.html.twig', array(
            'statsManager' => $statsManager,
            'collection' => $collection,
            'keyRecolnat' => AbstractDiff::KEY_RECOLNAT,
            'keyInstitution' => AbstractDiff::KEY_INSTITUTION
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
     * @param UserInterface|User $user
     * @param Request $request
     * @param string $institutionCode
     * @param string $collectionCode
     * @param string $selectedClassName
     * @param int $page
     * @return Response
     */
    public function viewDiffsAction(
        UserInterface $user,
        Request $request,
        $institutionCode,
        $collectionCode,
        $selectedClassName = 'all',
        $page = 1
    )
    {
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $user);
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($user)->setCollection($collection);
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

        $taxons = $exportManager->getDiffHandler()->getTaxons(array_keys($diffs['datas']));
        /** @var AbstractPagination $pagination */
        $pagination = $paginator->paginate($diffs['datas'], $page, $maxItemPerPage);
        $catalogNumbers = array_keys($pagination->getItems());
        $specimens = [];
        $specimens['recolnat'] = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findByCatalogNumbers($collection,
            $catalogNumbers, AbstractQuery::HYDRATE_OBJECT);
        $specimens['institution'] = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
            'buffer')->findByCatalogNumbers($collection, $catalogNumbers, AbstractQuery::HYDRATE_OBJECT);

        return $this->render('@App/Front/viewDiffs.html.twig', array(
            'collection' => $collection,
            'diffs' => $diffs,
            'specimens' => $specimens,
            'pagination' => $pagination,
            'exportManager' => $exportManager,
            'taxons' => $taxons,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/lonesomes/{db}/{selectedClassName}/{page}", name="lonesomes",
     * defaults={"selectedClassName" = "all", "page" = 1}, requirements={"page": "\d+", "db"="recolnat|institution"},
     * options={"expose"=true})
     *
     * @param UserInterface|User $user
     * @param Request $request
     * @param string $institutionCode
     * @param string $collectionCode
     * @param string $selectedClassName
     * @param int $page
     * @param string $db
     * @return Response
     */
    public function viewLonesomesAction(
        UserInterface $user,
        Request $request,
        $institutionCode,
        $collectionCode,
        $db,
        $selectedClassName = 'all',
        $page = 1
    )
    {
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $user);

        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($user)->setCollection($collection);
        $maxItemPerPage = $exportManager->getMaxItemPerPage($request);

        $lonesomeRecords = $exportManager->getDiffHandler()->getLonesomeRecords($db, $selectedClassName);

        $taxons = $exportManager->getDiffHandler()->getTaxons(array_keys($lonesomeRecords));

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($lonesomeRecords, $page, $maxItemPerPage);
        $catalogNumbers = array_keys($pagination->getItems());

        if ($db == 'recolnat') {
            $specimens = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')->findByCatalogNumbers($collection,
                $catalogNumbers, AbstractQuery::HYDRATE_OBJECT);
        } else {
            $specimens = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
                'buffer')->findByCatalogNumbers($collection, $catalogNumbers, AbstractQuery::HYDRATE_OBJECT);
        }

        return $this->render('@App/Front/viewLonesome.html.twig', array(
            'collection' => $collection,
            'specimens' => $specimens,
            'pagination' => $pagination,
            'db' => $db,
            'lonesomeRecords' => $lonesomeRecords,
            'taxons' => $taxons,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/specimen/tab/{catalogNumber}/{type}/{db}",
     *     requirements={"page": "\d+", "db"="recolnat|institution"}, name="tabSpecimen", options={"expose"=true})
     * @param UserInterface|User $user
     * @param string $institutionCode
     * @param string $collectionCode
     * @param string $catalogNumber
     * @param string $type
     * @param string $db
     * @return Response
     */
    public function viewSpecimenTabAction(UserInterface $user, $institutionCode, $collectionCode, $catalogNumber, $type, $db)
    {
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $user);
        if ($db == 'recolnat') {
            $specimen = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen')
                ->findOneByCatalogNumber($collection, $catalogNumber);
        } else {
            $specimen = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen',
                'buffer')->findOneByCatalogNumber($collection, $catalogNumber);
        }

        $template = 'tab-' . strtolower($type) . '.html.twig';

        return $this->render('@App/Front/partial/specimen/' . $template, array(
            'specimen' => $specimen,
            'catalogNumber' => $catalogNumber,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/specimens/view/{jsonCatalogNumbers}/{page}", name="viewSpecimens",
     * options={"expose"=true}, defaults={"page"= 1}, requirements={"page": "\d+"})
     * @param UserInterface|User $user
     * @param Request $request
     * @param string $institutionCode
     * @param string $collectionCode
     * @param string $jsonCatalogNumbers
     * @param int $page
     * @return Response
     */
    public function viewSpecimensAction(
        UserInterface $user,
        Request $request,
        $institutionCode,
        $collectionCode,
        $jsonCatalogNumbers,
        $page = 1
    )
    {
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $user);
        $exportManager = $this->get('exportmanager')->init($user)->setCollection($collection);

        $catalogNumbers = json_decode($jsonCatalogNumbers);

        list($pagination, $diffs, $specimens) = $this->getDataForDisplay($page, $catalogNumbers, $request,
            $exportManager, $collection);
        $diffs['taxons'] = $exportManager->getDiffHandler()->getTaxons($catalogNumbers);

        return $this->render('@App/Front/viewSpecimens.html.twig', array(
            'collection' => $collection,
            'diffs' => $diffs,
            'specimens' => $specimens,
            'exportManager' => $exportManager,
            'pagination' => $pagination,
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/search/{page}", name="search", defaults={"page"= 1},
     *                                                            requirements={"page": "\d+"})
     * @param UserInterface|User $user
     * @param String $institutionCode
     * @param String $collectionCode
     * @param Integer $page
     * @param Request $request
     * @return Response
     */
    public function searchAction(UserInterface $user, Request $request, $institutionCode, $collectionCode, $page = 1)
    {
        $search = $request->get('search', '');

        if (empty($search)) {
            return $this->redirectToRoute('viewfile', [
                    'institutionCode' => $institutionCode,
                    'collectionCode' => $collectionCode
                ]
            );
        }
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $user);

        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($user)->setCollection($collection);

        $catalogNumbers = $exportManager->getDiffHandler()->search($search);

        list($pagination, $diffs, $specimens) = $this->getDataForDisplay($page, $catalogNumbers, $request,
            $exportManager, $collection);

        $diffs['taxons'] = $exportManager->getDiffHandler()->getTaxons(array_keys($specimens));

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
     * @Route("{institutionCode}/{collectionCode}/list/{type}/{page}", name="list", defaults={"page"= 1},
     *                                                                 requirements={"page": "\d+"})
     * @param UserInterface|User $user
     * @param String $institutionCode
     * @param String $collectionCode
     * @param String $type
     * @return Response
     */
    public function listSpecimensAction(UserInterface $user, $institutionCode, $collectionCode, $type)
    {
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $user);
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->get('exportmanager')->init($user)->setCollection($collection);

        $specimens = [];
        $orderSpecimens = [];
        $orderSpecimensOutput = [];
        switch ($type) {
            case 'alpha':
                $specimens = $exportManager->getDiffs()['datas'];
                break;
            case 'selected':
                $catalogNumbers = $this->get('session')->get('selectedSpecimens');
                $specimens = $exportManager->orderDiffsByTaxon($exportManager->getDiffsByCatalogNumbers($catalogNumbers))['datas'];
                break;
        }
        $taxons = $exportManager->getDiffHandler()->getTaxons(array_keys($specimens));
        if (count($specimens)) {
            $withoutTaxon = [];
            foreach ($specimens as $catalogNumber => $specimen) {
                isset($taxons[$catalogNumber]) ? $taxon = $taxons[$catalogNumber] : $taxon = null;
                if (!(empty($taxon))) {
                    $firstLetter = mb_substr($taxon, 0, 1);
                    $letter = mb_strtoupper($firstLetter, 'UTF-8');
                    $orderSpecimens[$letter][$catalogNumber] = $specimen;
                } else {
                    $taxon = null;
                    $withoutTaxon[$catalogNumber] = $specimen;
                }
                if (count($withoutTaxon)) {
                    $orderSpecimensOutput = ['N/A' => $withoutTaxon] + $orderSpecimens;
                } else {
                    $orderSpecimensOutput = $orderSpecimens;
                }
            }
        }

        return $this->render('@App/Front/list.html.twig', array(
            'collection' => $collection,
            'exportManager' => $exportManager,
            'orderSpecimens' => $orderSpecimensOutput,
            'type' => $type,
            'taxons' => $taxons
        ));

    }


    /**
     * @param int $page
     * @param array $catalogNumbers
     * @param Request $request
     * @param ExportManager $exportManager
     * @param Collection $collection
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
            'buffer')->findByCatalogNumbers($collection, $catalogNumbers, AbstractQuery::HYDRATE_OBJECT);

        return array($pagination, $diffs, $specimens);
    }
}
