<?php

namespace AppBundle\Controller;

use AppBundle\Business\DiffHandler;
use AppBundle\Manager\DiffComputer;
use AppBundle\Manager\DiffManager;
use AppBundle\Manager\RecolnatServer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComputeController extends Controller
{
    /**
     * @Route("{collectionCode}/diff/configure/", name="searchDiff", options={"expose"=true})
     * @param string $collectionCode
     * @return Response
     */
    public function configureSearchDiffAction($collectionCode)
    {
        $collection = $this->get('utility')->getCollection($collectionCode);
        $institutionCode = $this->getUser()->getInstitutionCode();


    }

    /**
     * @Route("{collectionCode}/diff/search", name="searchDiff", options={"expose"=true})
     * @param string $collectionCode
     * @return Response
     */
    public function searchDiffAction($collectionCode)
    {
        $collection = $this->get('utility')->getCollection($collectionCode);

        $institutionCode = $this->getUser()->getInstitutionCode();
        $diffManager = $this->get('diff.manager');
        $diffManager->init($collection);

        $diffComputer = $this->get('diff.computer');
        $diffComputer->setCollection($collection);

        $diffHandler = new DiffHandler($this->getParameter('export_path').'/'.$institutionCode);
        $diffHandler->setCollectionCode($collectionCode);

        $response = new StreamedResponse();

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');


        $this->searchDiffSetCallBack($response, $diffManager, $diffComputer, $diffHandler);

        $response->send();
    }

    /**
     * @param StreamedResponse $response
     * @param DiffManager      $diffManager
     * @param DiffComputer     $diffComputer
     * @param DiffHandler      $diffHandler
     */
    private function searchDiffSetCallBack($response, $diffManager, $diffComputer, $diffHandler)
    {
        $response->setCallback(function() use ($diffManager, $diffComputer, $diffHandler) {
            $server = new RecolnatServer();
            $catalogNumbers = [];

            // Nb total d'étapes :  Search / Compute pour chaque entité
            // +1 étape sauvegarde
            $server->steps->send(count($diffManager::ENTITIES_NAME) * 2 + 1);
            $countStep = 0;
            foreach ($diffManager::ENTITIES_NAME as $entityName) {

                $server->step->send(json_encode(['count' => $countStep++, 'step' => $entityName.' : recherche']));
                $catalogNumbers[$entityName] = $diffManager->getDiff($entityName);
                $server->step->send(json_encode(['count' => $countStep++, 'step' => $entityName.' : traitement']));

                $diffComputer->setCatalogNumbers($catalogNumbers);
                $diffComputer->computeClassname($entityName);
            }

            $datas = $diffComputer->getAllDatas();

            $server->step->send(json_encode(['count' => $countStep++, 'step' => 'save']));
            $diffHandler->saveDiffs($datas);
            $server->step->send(json_encode(['count' => $countStep++, 'step' => 'done']));
        });
    }
}
