<?php

namespace AppBundle\Controller;

use AppBundle\Business\DiffHandler;
use AppBundle\Manager\DiffComputer;
use AppBundle\Manager\DiffManager;
use AppBundle\Manager\RecolnatServer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ComputeController extends Controller
{
    /**
     * @Route("{collectionCode}/diff/configure/", name="configureSearchDiff", options={"expose"=true})
     * @param string  $collectionCode
     * @param Request $request
     * @return Response
     */
    public function configureSearchDiffAction(Request $request, $collectionCode)
    {
        $collection = $this->get('utility')->getCollection($collectionCode);

        $defaults = array(
            'startDate' => new \DateTime('today'),
        );

        $form = $this->createFormBuilder($defaults)
            ->add('startDate', DateType::class, ['label' => 'label.startDate'])
            ->add('cookieTGC', HiddenType::class, ['attr' => ['class' => 'js-cookieTGC']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            if (empty($data['cookieTGC'])) {
                throw new AccessDeniedException('cookieTGC is empty - javascript must be enabled');
            }

            return $this->redirectToRoute('newSearchDiff',
                [
                    'collectionCode' => $collectionCode,
                    'startDate' => $data['startDate']->getTimestamp(),
                    'cookieTGC' => $data['cookieTGC'],
                ]);
        }

        return $this->render('@App/Compute/configure.html.twig', [
            'form' => $form->createView(),
            'collection' => $collection
        ]);
    }

    /**
     * @Route("{collectionCode}/newSearchDiff/{startDate}/{cookieTGC}", name="newSearchDiff")
     * @param string $collectionCode
     * @param int    $startDate
     * @param string $cookieTGC
     * @return Response
     */
    public function newSearchDiffAction($collectionCode, $startDate, $cookieTGC)
    {
        $command = $this->get('command.search_diffs');
        $command->setContainer($this->container);

        $params = [
            'startDate' => (\DateTime::createFromFormat('U', $startDate)->format('d/m/Y')),
            'username' => $this->getUser()->getUsername(),
            'collectionCode' => $collectionCode
        ];

        $consoleDir = realpath('/'.$this->get('kernel')->getRootDir().'/../bin/console');
        $command = sprintf('%s diff:search -vvv %s %s %s --cookieTGC=%s',
            $consoleDir, $params['startDate'], $params['collectionCode'], $params['username'], $cookieTGC);

        $process = new Process($command);
        $process->setTimeout(null);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this->redirectToRoute('viewfile', ['collectionCode' => $collectionCode]);

    }

    /**
     * @Route("{collectionCode}/diff/search", name="searchDiff", options={"expose"=true})
     * @param string $collectionCode
     * @return Response
     */
    public function searchDiffAction($collectionCode)
    {
        $collection = $this->get('utility')->getCollection($collectionCode);

        $diffManager = $this->get('diff.manager');
        $diffManager->init($collection);

        $diffComputer = $this->get('diff.computer');
        $diffComputer->setCollection($collection);

        $diffHandler = new DiffHandler($this->getUser()->getDataDirPath(), $collection,
            $this->getParameter('user_group'));

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
            $server->step->send(json_encode(['count' => $countStep, 'step' => 'done']));
        });
    }
}
