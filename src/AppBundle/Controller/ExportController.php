<?php

namespace AppBundle\Controller;

use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Business\Process;
use AppBundle\Business\User\User;
use AppBundle\Form\Type\ExportPrefsType;
use AppBundle\Manager\AbstractDiff;
use AppBundle\Manager\RecolnatServer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class ExportController extends Controller
{

    /**
     * @Route("{institutionCode}/{collectionCode}/export/setPrefs/{type}", name="setPrefsForExport",
     *     requirements={"type"="dwc|csv"})
     * @param UserInterface|User $user
     * @param Request $request
     * @param string $institutionCode
     * @param string $collectionCode
     * @param string $type
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function setPrefsForExportAction(UserInterface $user, Request $request, $institutionCode, $collectionCode, $type)
    {
        $collection = $this->get('utility')->getCollection($institutionCode, $collectionCode, $user);
        $statsManager = $this->get('statsmanager')->init($user, $collection);

        $exportPrefs = new ExportPrefs();

        $form = $this->createForm(ExportPrefsType::class, $exportPrefs, [
            'action' => $this->generateUrl('setPrefsForExport', [
                'institutionCode' => $institutionCode,
                'collectionCode' => $collectionCode,
                'type' => $type
            ]),
            'attr' => [
                'class' => 'js-formExport',
            ]
        ]);
        $form->add('cookieTGC', HiddenType::class, ['attr' => ['class' => 'js-cookieTGC']]);
        $form->add('collectionCode', HiddenType::class,
                ['attr' => ['class' => 'js-collectionCode'], 'mapped' => false, 'data'=>$collectionCode])
            ->add('institutionCode', HiddenType::class,
                ['attr' => ['class' => 'js-institutionCode'], 'mapped' => false, 'data'=>$institutionCode]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            set_time_limit(0);
            $paramsExport = [
                'collectionCode' => $collectionCode,
                'institutionCode' => $institutionCode,
                'exportPrefs' => serialize($exportPrefs)
            ];
            switch ($type) {
                case 'dwc':
                    return $this->redirectToRoute('export',
                        array_merge($paramsExport, ['type' => 'dwc']));
                case 'csv':
                    return $this->redirectToRoute('export',
                        array_merge($paramsExport, ['type' => 'csv']));
            }
        }

        $sumStats = $statsManager->getSumStats();
        $statsChoices = $statsManager->getStatsChoices();
        $sumLonesomeRecords = $statsManager->getSumLonesomeRecords();


        return $this->render('@App/Export/setPrefsForExport.html.twig', array(
            'collection' => $collection,
            'sumStats' => $sumStats,
            'statsChoices' => $statsChoices,
            'sumLonesomeRecords' => $sumLonesomeRecords,
            'form' => $form->createView(),
            'keysRef' => AbstractDiff::getKeysRef(),
            'type' => $type
        ));
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/export/{type}/", name="export", requirements={"type": "csv|dwc"},
     *     options={"expose"=true})
     * @param UserInterface|User $user
     * @param string $type
     * @param Request $request
     * @param string $collectionCode
     * @param string $institutionCode
     * @return JsonResponse
     * @throws \Exception
     */
    public function exportAction(UserInterface $user, $institutionCode, $type, $collectionCode, Request $request)
    {
        $username = $user->getUsername();
        parse_str($request->get('exportPrefs'), $params);
        $exportPrefs=new ExportPrefs();
        $exportPrefs->setCookieTGC($params['export_prefs']['cookieTGC']);
        $exportPrefs->setSideForChoicesNotSet($params['export_prefs']['sideForChoicesNotSet']);
        $exportPrefs->setSideForNewRecords($params['export_prefs']['sideForNewRecords']);

        $consoleDir = realpath('/' . $this->get('kernel')->getRootDir() . '/../bin/console');
        $command = sprintf('%s diff:export %s %s %s %s --cookieTGC=%s',
            $consoleDir, $institutionCode, $collectionCode, $type, $username, $exportPrefs->getCookieTGC());

        /*dump($command);
        return $this->render('@App/base.html.twig');*/
        $process = new Process($command);
        $process->setTimeout(null);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');

        $this->exportDiffSetCallBack($response, $process);
        $response->send();
        $file = trim($process->getOutput());

        return new JsonResponse(['file' => urlencode($file)]);
    }


    /**
     * @param StreamedResponse $response
     * @param Process $process
     */
    private function exportDiffSetCallBack(StreamedResponse $response, Process $process)
    {
        $response->setCallback(function () use ($process) {
            $server = new RecolnatServer();
            $progress = 0;
            $server->step->send(\json_encode(['name' => 'general', 'progress' => $progress]));
            $process->run(function ($type, $buffer) use ($server, &$progress, $process) {
                //$this->get('logger')->addAlert('log step : '.$buffer);
                if (strpos($buffer, '}' . PHP_EOL . '{') && !empty($buffer)) {
                    $validJson = '[' . str_replace('}' . PHP_EOL . '{', '},{', $buffer) . ']';
                    $arrayJson = \json_decode($validJson);
                    foreach ($arrayJson as $value) {
                        $this->sendStep($server, \json_encode($value));
                    }
                }
                else {
                    $this->sendStep($server, $buffer);
                }
            });
            $server->close->send(true);
        });
    }

    private function sendStep($server, $buffer)
    {
     if (!is_null($buffer)) {
            try{
                $bufferDecode = \json_decode($buffer);
                if (isset($bufferDecode->total)) {
                    $server->total->send($buffer);
                } elseif (isset($bufferDecode->file)) {
                    $server->file->send($buffer);
                } else {
                    $server->step->send($buffer);
                }
            }
            catch(\Exception $e){
                $this->get('logger')->addAlert('json decode erreur : '.$buffer);
            }
        }
    }
}
