<?php

namespace AppBundle\Controller;

use AppBundle\Business\DiffHandler;
use AppBundle\Business\User\User;
use AppBundle\Manager\AbstractDiff;
use AppBundle\Manager\DiffComputer;
use AppBundle\Manager\DiffManager;
use AppBundle\Manager\RecolnatServer;
use AppBundle\Manager\UtilityService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class ComputeController extends Controller
{
    /**
     * @Route("{institutionCode}/{collectionCode}/diff/configure/", name="configureSearchDiff", options={"expose"=true})
     * @param string  $institutionCode
     * @param string  $collectionCode
     * @param Request $request
     * @return Response
     */
    public function configureSearchDiffAction(Request $request, UserInterface $user, $institutionCode, $collectionCode)
    {
        $collection = $this->get(UtilityService::class)->getCollection($institutionCode, $collectionCode, $user);

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
                /*return $this->redirectToRoute('searchDiffStreamed',
                    [
                        'institutionCode' => $institutionCode,
                        'collectionCode' => $collectionCode,
                        'startDate' => $data['startDate']->getTimestamp(),
                        'cookieTGC' => $data['cookieTGC'],
                    ]);*/
                return $this->redirectToRoute('searchDiffDebug',
                    [
                        'institutionCode' => $institutionCode,
                        'collectionCode' => $collectionCode,
                        'startDate' => $data['startDate']->getTimestamp(),
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
     * @param UserInterface|User $user
     * @param string $institutionCode
     * @param string $collectionCode
     * @param string $startDate
     * @param string $cookieTGC
     * @return Response
     */
    public function searchDiffActionStreamedAction(UserInterface $user, $institutionCode, $collectionCode, $startDate, $cookieTGC)
    {
        $collection = $this->get(UtilityService::class)->getCollection($institutionCode, $collectionCode, $user);

        $startDate = \DateTime::createFromFormat('U', $startDate)->format('d/m/Y');
        $username = $user->getUsername();

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
     * @Route("diff/search/error/", name="searchDiffError", options={"expose"=true})
     * @return Response
     */
    public function searchDiffErrorAction()
    {
        return $this->render('@App/Front/searchDiffError.html.twig');
    }

    /**
     * @Route("{institutionCode}/{collectionCode}/searchDiffDebug/{startDate}/", name="searchDiffDebug",
     *                                                                                         options={"expose"=true})
     * @param UserInterface|User $user
     * @param string $institutionCode
     * @param string $collectionCode
     * @param string $startDate
     * @return Response
     * @throws \Exception
     */
    public function debugSearchDiffAction(UserInterface $user, $institutionCode, $collectionCode, $startDate)
    {
        $collection = $this->get(UtilityService::class)->getCollection($institutionCode, $collectionCode);
        $diffHandler = new DiffHandler($user->getDataDirPath(), $collection,
            $this->getParameter('user_group'));
        $collectionPath = $diffHandler->getCollectionPath();

        $dateTime = new \DateTime();
        $dateTime->setTimestamp($startDate);
        $startDate = $dateTime->format('d/m/Y');
        if (UtilityService::isDateWellFormatted($startDate)) {
            $startDate = \DateTime::createFromFormat('d/m/Y', $startDate);
        } else {
            throw new \Exception($this->get('translator')->trans('access.denied.wrongDateFormat', [], 'exceptions'));
        }

        $diffManager = $this->get(DiffManager::class);
        $diffManager->setCollectionCode($collectionCode);
        $diffManager->setStartDate($startDate);
        $diffManager->harvestDiffs();

        $diffComputer = $this->get(DiffComputer::class);
        $diffComputer->setCollection($collection);

        $catalogNumbersFiles = $this->createCatalogNumbersFiles($diffManager, $diffHandler);

        try {
            foreach ($diffManager::ENTITIES_NAME as $entityName) {
                $this->searchDiff($institutionCode, $collectionCode, $collectionPath, $entityName);
            }

            $mergeResult = $this->mergeFiles($diffManager::ENTITIES_NAME, $diffHandler->getCollectionPath());

            dump($mergeResult);
            $this->cleanDiffs($mergeResult);
            $diffHandler->saveData($mergeResult['data']);
            $diffHandler->saveTaxons($mergeResult['taxons']);

            $this->removeCatalogNumbersFiles($catalogNumbersFiles);
        } catch (ProcessFailedException $e) {
            throw $e;
        }

        return $this->render('@App/base.html.twig');
    }


    /**
     * @param DiffManager $diffManager
     * @param DiffHandler $diffHandler
     * @return array
     */
    private function createCatalogNumbersFiles(DiffManager $diffManager, DiffHandler $diffHandler)
    {
        $catalogNumbersFiles = [];
        $fs = new Filesystem();
        foreach ($diffManager::ENTITIES_NAME as $entityName) {
            $catalogNumbers = $diffManager->getResultByClassName($entityName);
            $catalogNumbersFilename = $diffHandler->getCollectionPath().'/catalogNumbers_'.$entityName.'.json';
            $fs->dumpFile($catalogNumbersFilename, \json_encode($catalogNumbers));
            $catalogNumbersFiles[] = $catalogNumbersFilename;
        }


        return $catalogNumbersFiles;
    }

    protected function searchDiff($institutionCode, $collectionCode, $savePath, $entityName)
    {
        $diffComputer = $this->get(DiffComputer::class);
        $collection = $this->get(UtilityService::class)->getCollection(
            $institutionCode,
            $collectionCode
        );

        if (!is_null($collection)) {

            $fileCatalogNumbers = new \SplFileObject($savePath.'/catalogNumbers_'.$entityName.'.json');
            $catalogNumbers[$entityName] = json_decode(file_get_contents($fileCatalogNumbers->getPathname()), true);

            $diffComputer->setCollection($collection);

            $diffComputer->setCatalogNumbers($catalogNumbers);
            $diffComputer->computeClassname($entityName);

            $datas = $diffComputer->getAllDatas();

            $fs = new Filesystem();
            $fs->dumpFile($savePath.'/'.$entityName.'.json', \json_encode($datas, JSON_PRETTY_PRINT));
            $fs->dumpFile($savePath.'/taxons_'.$entityName.'.json', \json_encode($diffComputer->getTaxons()));
        }
    }

    private function mergeFiles(array $entityNames, $path)
    {
        $mergeData = [];
        $mergeTaxons = [];
        foreach ($entityNames as $entityName) {
            $dataPathName = $path.'/'.$entityName.'.json';
            $taxonsPathName = $path.'/taxons_'.$entityName.'.json';
            $datas = json_decode(file_get_contents($dataPathName), true);
            $taxons = json_decode(file_get_contents($taxonsPathName), true);
            unlink($dataPathName);
            unlink($taxonsPathName);
            $mergeData = $this->arrayMergeRecursiveDistinct($mergeData, $datas);
            $mergeTaxons = $this->arrayMergeRecursiveDistinct($mergeTaxons, $taxons);
        }
        $this->filterLonesomesRecords($mergeData['lonesomeRecords'], $mergeData['statsLonesomeRecords']);

        return ['data' => $mergeData, 'taxons' => $mergeTaxons];
    }

    private function arrayMergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset ($merged [$key]) && is_array($merged [$key])) {
                $merged [$key] = $this->arrayMergeRecursiveDistinct($merged [$key], $value);
            } else {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }

    public function filterLonesomesRecords(array &$lonesomesRecords, array &$statsLonesomeRecords)
    {
        $keyRecolnat = AbstractDiff::KEY_RECOLNAT;
        $keyInstitution = AbstractDiff::KEY_INSTITUTION;
        $specimens = $lonesomesRecords['Specimen'];
        $catalogNumbersSpecimen = [$keyRecolnat => [], $keyInstitution => []];
        $catalogNumbersSpecimen[$keyRecolnat] = array_column($specimens[$keyRecolnat], 'catalogNumber');
        $catalogNumbersSpecimen[$keyInstitution] = array_column($specimens[$keyInstitution], 'catalogNumber');


        foreach ($lonesomesRecords as $entityName => $records) {
            if ($entityName !== 'Specimen') {
                if (count($records[$keyRecolnat])) {
                    foreach ($records[$keyRecolnat] as $key => $record) {
                        if (in_array($record['catalogNumber'], $catalogNumbersSpecimen[$keyRecolnat])) {
                            unset($lonesomesRecords[$entityName][$keyRecolnat][$key]);
                        }
                    }
                }
                if (count($records[$keyInstitution])) {
                    foreach ($records[$keyInstitution] as $key => $record) {
                        if (in_array($record['catalogNumber'], $catalogNumbersSpecimen[$keyInstitution])) {
                            unset($lonesomesRecords[$entityName][$keyInstitution][$key]);
                        }
                    }
                }
            }
        }
        $statsLonesomeRecords = DiffComputer::computeStatsLonesomeRecords($lonesomesRecords);
    }

    private function removeCatalogNumbersFiles(array $catalogNumbersFiles)
    {
        foreach ($catalogNumbersFiles as $catalogNumbersFile) {
            if (is_file($catalogNumbersFile)) {
                unlink($catalogNumbersFile);
            }
        }
    }


    /**
     * Parcourt le tableau des enregistrements et supprime toutes les classes qui ont été détectées comme lonesomes
     * si un enregistrement specimen est déjà qualifié de lonesome
     */
    private function cleanDiffs(&$diffs) {
        if ($diffs['data']['datas']) {
            foreach ($diffs['data']['datas'] as $catalogNumber => $rows) {
                if (isset($rows['Specimen'], $rows['Specimen']['lonesomes'])) {
                    $diffs['data']['datas'][$catalogNumber] = [];
                    $diffs['data']['datas'][$catalogNumber]['Specimen'] = $rows['Specimen'];
                    $this->cleanClasses($diffs, $catalogNumber);
                }
            }
        }
    }

    private function cleanClasses(&$diffs, $catalogNumber)
    {
        foreach ($diffs['data']['classes'] as $className => $rows) {
            if ($className !== 'Specimen' && in_array($catalogNumber, $rows, false)) {
                $rows = array_flip($rows);
                unset($rows[$catalogNumber]);
                $diffs['data']['classes'][$className] = array_values(array_flip($rows));
            }
        }
    }
}
