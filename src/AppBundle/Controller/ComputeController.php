<?php

namespace AppBundle\Controller;

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
     * @Route("{institutionCode}/{collectionCode}/diff/configure/", name="configureSearchDiff", options={"expose"=true})
     * @param string  $institutionCode
     * @param string  $collectionCode
     * @param Request $request
     * @return Response
     */
    public function configureSearchDiffAction(Request $request, $institutionCode, $collectionCode)
    {
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode);

        $defaults = array(
            'startDate' => new \DateTime('today'),
            'collectionCode' => $collectionCode,
            'institutionCode' => $institutionCode
        );

        $form = $this->createFormBuilder($defaults,
            [
                'attr' => [
                    'class' => 'js-formSearch',
                ]
            ])
            ->add('startDate', DateType::class, ['label' => 'label.startDate'])
            ->add('cookieTGC', HiddenType::class, ['attr' => ['class' => 'js-cookieTGC']])
            ->add('collectionCode', HiddenType::class, ['attr' => ['class' => 'js-collectionCode']])
            ->add('institutionCode', HiddenType::class, ['attr' => ['class' => 'js-institutionCode']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            if (empty($data['cookieTGC'])) {
                throw new AccessDeniedException('cookieTGC is empty - javascript must be enabled');
            }

            if ($data['startDate'] instanceof \DateTime) {
                return $this->redirectToRoute('searchDiffStreamed',
                    [
                        'institutionCode' => $institutionCode,
                        'collectionCode' => $collectionCode,
                        'startDate' => $data['startDate']->getTimestamp(),
                        'cookieTGC' => $data['cookieTGC'],
                    ]);
            }
        }

        return $this->render('@App/Compute/configure.html.twig', [
            'form' => $form->createView(),
            'collection' => $collection
        ]);
    }


    /**
     * @Route("{institutionCode}/{collectionCode}/searchDiffStreamed/{startDate}/{cookieTGC}", name="searchDiffStreamed",
     *                                                                                         options={"expose"=true})
     * @param string $institutionCode
     * @param string $collectionCode
     * @param string $startDate
     * @param string $cookieTGC
     * @return Response
     */
    public function searchDiffActionStreamedAction($institutionCode, $collectionCode, $startDate, $cookieTGC)
    {
        $startDate = \DateTime::createFromFormat('U', $startDate)->format('d/m/Y');
        $username = $this->getUser()->getUsername();

        $consoleDir = realpath('/'.$this->get('kernel')->getRootDir().'/../bin/console');
        $command = sprintf('%s diff:search %s %s %s %s --cookieTGC=%s',
            $consoleDir, $startDate, $institutionCode, $collectionCode, $username, $cookieTGC);

        $process = new Process($command);
        $process->setTimeout(null);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');

        $this->searchDiffSetCallBack($response, $process);
        $response->send();


        return $this->redirectToRoute('viewfile', ['collectionCode' => $collectionCode]);

    }

    /**
     * @param StreamedResponse $response
     * @param Process          $process
     */
    private function searchDiffSetCallBack(StreamedResponse $response, Process $process)
    {
        $response->setCallback(function() use ($process) {
            $server = new RecolnatServer();
            $progress = 0;
            $server->step->send(\json_encode(['name' => 'general', 'progress' => $progress]));
            $process->run(function($type, $buffer) use ($server, &$progress) {
                $step = 100 / count(DiffManager::ENTITIES_NAME);
                if (Process::ERR === $type) {
                    $server->error->send($buffer);
                } else {
                    $data = \json_decode($buffer);
                    // Cas ou des retours sont reçus simultanément
                    if (is_null($data)) {
                        $validJson = '['.str_replace('}'.PHP_EOL.'{', '},{', $buffer).']';
                        $arrayJson = \json_decode($validJson);
                        foreach ($arrayJson as $value) {
                            $server->step->send(\json_encode($value));
                        }

                    } else {
                        $server->step->send($buffer);
                    }

                    if (isset($data->progress) && $data->progress == 100) {
                        $progress += $step;
                    }
                    $server->step->send(\json_encode(['name' => 'general', 'progress' => $progress]));
                }
            });
            $server->step->send(\json_encode(['name' => 'general', 'progress' => 100]));
            $server->stop->send(\json_encode(['name' => 'general', 'progress' => 100]));
        });
    }


    /**
     * @Route("{collectionCode}/searchDiff/{startDate}/{cookieTGC}", name="newSearchDiff")
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
            'startDate' => (\DateTime::createFromFormat('U', $startDate)->format('dmY')),
            'username' => $this->getUser()->getUsername(),
            'collectionCode' => $collectionCode
        ];

        $consoleDir = realpath('/'.$this->get('kernel')->getRootDir().'/../bin/console');
        $command = sprintf('%s diff:search -vvv %s %s %s --cookieTGC=%s',
            $consoleDir, $params['startDate'], $params['collectionCode'], $params['username'], $cookieTGC);

        $process = new Process(escapeshellcmd($command));
        $process->setTimeout(null);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this->redirectToRoute('viewfile', ['collectionCode' => $collectionCode]);

    }


    /**
     * @Route("diff/search/error/", name="searchDiffError", options={"expose"=true})
     * @return Response
     */
    public function searchDiffErrorAction()
    {
        return $this->render('@App/Front/searchDiffError.html.twig');
    }
}
