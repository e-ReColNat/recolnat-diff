<?php

namespace AppBundle\Command;

use AppBundle\Business\DiffHandler;
use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Business\Process;
use AppBundle\Business\User\User;
use AppBundle\Entity\Collection;
use AppBundle\Manager\ExportManager;
use AppBundle\Manager\GenericEntityManager;
use AppBundle\Manager\ProcessManager;
use epierce\CasRestClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Translator;

class ExportCommand extends ContainerAwareCommand
{
    private $serverLoginUrl;
    private $serverTicket;
    private $requestOptions;
    private $apiRecolnatBaseUri;
    private $collectionCode;
    private $institutionCode;
    private $apiRecolnatUserPath;
    /** @var  User */
    private $user;
    /** @var  Translator */
    private $translator;
    
    /** @var  ExportManager */
    private $exportManager;
    
    /** @var  ExportPrefs */
    private $exportPrefs;

    /** @var  GenericEntityManager */
    private $genericEntityManager;

    /** @var  Collection */
    private $collection;

    private $maxNbSpecimenPerPass;

    /** @var  \SplFileObject|null */
    private $logFile;

    private $logFileTemplate = 'log-%s-%s-%s.txt';

    private $collectionPath;

    private $debug = false;

    public function __construct(
        $serverLoginUrl,
        $serverTicket,
        $requestOptions,
        $apiRecolnatBaseUri,
        $apiRecolnatUserPath,
        $maxNbSpecimenPerPass,
        GenericEntityManager $genericEntityManager
    ) {
        $this->serverLoginUrl = $serverLoginUrl;
        $this->serverTicket = $serverTicket;
        $this->requestOptions = $requestOptions;
        $this->apiRecolnatBaseUri = $apiRecolnatBaseUri;
        $this->apiRecolnatUserPath = $apiRecolnatUserPath;
        $this->genericEntityManager = $genericEntityManager;
        $this->maxNbSpecimenPerPass = $maxNbSpecimenPerPass;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('diff:export')
            ->setDescription('Export diff in CSV or DWC format')
            ->addArgument(
                'institutionCode',
                InputArgument::REQUIRED,
                'institution code ?'
            )
            ->addArgument(
                'collectionCode',
                InputArgument::REQUIRED,
                'collection code ?'
            )
            ->addArgument(
                'format',
                InputArgument::REQUIRED,
                'format : csv or dwc'
            )
            ->addArgument(
                'username',
                InputOption::VALUE_REQUIRED,
                'login ?'
            )
            ->addOption(
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'password ?'
            )
            ->addOption(
                'cookieTGC',
                null,
                InputOption::VALUE_REQUIRED,
                'cookieTGC for cas Authentication'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->collectionCode = $input->getArgument('collectionCode');
        $this->institutionCode = $input->getArgument('institutionCode');
        $this->translator = $this->getContainer()->get('translator');
        $this->user = $this->getUser($input);
        $this->collection = $this->getContainer()->get('utility')
            ->getCollection($this->institutionCode, $this->collectionCode, $this->user);

        $this->setLogFilePath();

        $diffHandler = new DiffHandler($this->user->getDataDirPath(), $this->collection,
            $this->getContainer()->getParameter('user_group'));
        $this->collectionPath = $diffHandler->getCollectionPath();

        /** @var ExportPrefs $this->exportPrefs */
        $this->exportPrefs = new ExportPrefs();
        $this->exportPrefs->setSideForNewRecords('recolnat');
        $this->exportPrefs->setSideForChoicesNotSet('recolnat');

        if (!($this->exportPrefs instanceof ExportPrefs)) {
            throw new \Exception('parameters must be an instance of ExportPrefs');
        }
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $this->exportManager = $this->getContainer()->get('exportmanager')->init($this->user)->setCollection($this->collection);

        $datasWithChoices = $this->getDiffRecords($output);
        $datasWithChoices = array_merge($datasWithChoices, $this->getLonesomesRecords($output));

        $file = $this->exportManager->export($datasWithChoices, $input->getArgument('format'), $this->exportPrefs);
        $output->writeln(\json_encode(['file' => $file]));
    }

    private function getDiffRecords(OutputInterface $output) {
        $catalogNumbers = $this->exportManager->getDiffCatalogNumbers();

        return $this->getFormattedDatas($output, $catalogNumbers, $this->exportPrefs->getSideForChoicesNotSet(), 'diffs');

    }

    /**
     * @param OutputInterface $output
     * @return array
     */
    private function getLonesomesRecords(OutputInterface $output) {
        if ($this->exportPrefs->getSideForNewRecords() != 'both') {
            $side = $this->exportPrefs->getSideForNewRecords();
        } // des deux côtés
        else {
            $side = 'recolnat';
        }
        
        $lonesomeRecords = $this->exportManager->diffHandler->getLonesomeRecordsFile()
            ->getLonesomeRecordsByBase($side);

        $catalogNumbers = array_keys($lonesomeRecords);
        if ($this->debug) {
            $catalogNumbers = array_slice($catalogNumbers, 0, 3500);
        }
        $formattedDatas = $this->getFormattedDatas($output, $catalogNumbers, $side, 'lonesomes');

        return $formattedDatas;
    }

    /**
     * @param OutputInterface $output
     * @param $catalogNumbers
     * @param $side
     * @return array
     */
    private function getFormattedDatas(OutputInterface $output, $catalogNumbers, $side, $type)
    {
        $formattedDatas = [];

        if (count($catalogNumbers)) {

            $arrayChunkCatalogNumbers = array_chunk($catalogNumbers, $this->maxNbSpecimenPerPass);

            $output->writeln(\json_encode(['name' => 'export_'.$type, 'total' => count($catalogNumbers), 'steps'=>count($arrayChunkCatalogNumbers)]));

            $datas = $this->launchHarvestProcesses($output, $arrayChunkCatalogNumbers, $side, $type);

            if ($type=='diffs') {
                $formattedDatas = $this->exportManager->getArrayDatasWithChoices($datas);
            }
            else {
                foreach ($datas as $catalogNumber => $specimen) {
                    $arraySpecimenWithEntities = $this->genericEntityManager->formatArraySpecimenForExport($specimen);
                    $formattedDatas[$catalogNumber] = $arraySpecimenWithEntities;
                }
            }
        }
        else {
            $output->writeln(\json_encode(['name' => 'export_'.$type, 'total' => count($catalogNumbers), 'steps'=>0]));
        }
        return $formattedDatas;
    }

    /**
     * @param array $filePaths
     * @return array
     */
    private function mergeFiles(array $filePaths) {
        $mergeData = [];
        $utilityService = $this->getContainer()->get('utility');
        if (count($filePaths)) {
            foreach ($filePaths as $filePath) {
                $datas = json_decode(file_get_contents($filePath), true);
                $mergeData=$utilityService::arrayMergeRecursiveDistinct($mergeData, $datas);
            }
        }
        return $mergeData;
    }


    private function launchHarvestProcesses(
        OutputInterface $output, $arrayChunkCatalogNumbers, $side, $type
    ) {
        $processes = [];
        $filePaths = [];
        $consoleDir = realpath('/'.$this->getContainer()->get('kernel')->getRootDir().'/../bin/console');

        foreach ($arrayChunkCatalogNumbers as $key=>$chunkCatalogNumbers) {
            $filePath = $this->collectionPath.'/'.$type.'_'.$key.'.json';
            $process = $this->getHarvestProcess($consoleDir, $filePath, $type, $key, $side, $chunkCatalogNumbers);
            $processes[] = $process;
            $filePaths[] = $filePath;
        }

        $processManager = new ProcessManager();
        $maxParallelProcesses = 8;
        $pollingInterval = 1000; // microseconds
        $processManager->runParallel($processes, $maxParallelProcesses, $pollingInterval, $output, $this->getLogFile());

        $utilityService = $this->getContainer()->get('utility');
        $datas = $this->mergeFiles($filePaths);
        $utilityService::removeFiles($filePaths);

        return $datas;
    }

    /**
     * @param $arrayChunkCatalogNumbers array
     * @param $side string
     * @return Process
     */
    private function getHarvestProcess($consoleDir, $filePath, $type, $key, $side, $chunkCatalogNumbers)
    {
        if ($this->debug) {
            //dump($chunkCatalogNumbers);
        }
        $command = sprintf('%s export:harvest_data %s %s %s %s %s',
            $consoleDir,
            $this->institutionCode,
            $this->collectionCode,
            $side,
            $filePath,
            implode(' ',$chunkCatalogNumbers));

        $process = new Process($command);
        $process->setName('export_'.$type);
        $process->setTimeout(null);
        $process->setKey($key);

        return $process;
    }
    /**
     * @param InputInterface $input
     * @return User
     */
    private function getUser(InputInterface $input)
    {
        if (!empty($input->getOption('cookieTGC'))) {
            $user = $this->userCasTicketVerification($input);

        } else {
            $user = $this->userCasAuthentication($input->getArgument('username'), $input->getOption('password'));
        }

        if (!$user->isManagerFor($this->collectionCode)) {
            /*$this->log($this->translator->trans($input->getArgument('username').' '.'access.denied.wrongPermission', [],
                'exceptions'));
            throw new AccessDeniedException($this->translator->trans('access.denied.wrongPermission', [],
                'exceptions'));*/
        }
        $user->init($this->getContainer()->getParameter('export_path'));

        return $user;
    }

    /**
     * @param InputInterface $input
     * @return User
     * @throws AccessDeniedException
     */
    private function userCasTicketVerification(InputInterface $input)
    {
        $cookieTGC = $input->getOption('cookieTGC');
        $username = $input->getArgument('username');
        if (!empty($cookieTGC) && !empty($username)) {
            $user = new User($username, $this->getContainer()->getParameter('api_recolnat_base_uri'),
                $this->getContainer()->getParameter('api_recolnat_user_path'), []);
            $verifySsl = true;
            $requestOptions = $this->getContainer()->getParameter('request_options');
            if (isset($requestOptions['verify']) && !$requestOptions['verify']) {
                $verifySsl = false;
            }
            try {
                $user->isGrantedByCheckServiceTicket(
                    $cookieTGC,
                    $this->getContainer()->getParameter('server_login_url'),
                    $this->getContainer()->getParameter('api_recolnat_server_ticket_path'),
                    $this->getContainer()->getParameter('api_recolnat_auth_service_url'),
                    $verifySsl);
            } catch (\Exception $e) {
                /*$this->log($input->getArgument('username').' '.$this->translator->trans('access.denied.wrongPermission',
                        [], 'exceptions'));*/
                throw new AccessDeniedException($this->translator->trans('access.denied.wrongPermission', [],
                    'exceptions'));
            }
        } else {
            /*$this->log($input->getArgument('username').' '.$this->translator->trans('access.denied.tgc_username', [],
                    'exceptions'));*/
            throw new AccessDeniedException($this->translator->trans('access.denied.tgc_username', [], 'exceptions'));
        }

        return $user;
    }

    /**
     * @param string $username
     * @param string $password
     * @return User
     * @throws \Exception
     */
    private function userCasAuthentication($username, $password)
    {
        $client = new CasRestClient();
        $client->setCasServer($this->serverLoginUrl);
        $client->setCasRestContext($this->serverTicket);
        $client->setCredentials($username, $password);
        $client->verifySSL($this->verifySsl());

        if ($client->login()) {
            $response = $client->post($this->getServiceRoute());
            if ($response->getStatusCode() == 200) {
                $user = new User($username, $this->apiRecolnatBaseUri, $this->apiRecolnatUserPath,
                    $this->getContainer()->getParameter('user_group'));

                return $user;
            } else {
                /*$this->log($this->translator->trans($username.' access.denied.wrongPermission', [], 'exceptions'));*/
                throw new AccessDeniedException($this->getContainer()->get('translator')
                    ->trans('access.denied.wrongPassword', [], 'exceptions'));
            }
        } else {
            /*$this->log($this->translator->trans($username.' access.denied.wrongPermission', [], 'exceptions'));*/
            throw new AccessDeniedException($this->getContainer()->get('translator')
                ->trans('access.denied.wrongPassword', [], 'exceptions'));
        }
    }

    /**
     * Retourne une route valide (le composant router de symfony 3.1 buggue en cli !)
     * @return string
     */
    private function getServiceRoute()
    {
        $context = $this->getContainer()->get('router')->getContext();

        $path = $this->getContainer()->get('router')->generate('index');
        if ($path{0} == '/') {
            $path = substr($path, 1);
        }
        $pattern = '%1$s://%2$s/%3$s';
        if (!empty($context->getBaseUrl())) {
            $pattern = '%1$s://%2$s/%4$s/%3$s';
        }

        return sprintf($pattern, $context->getScheme(), $context->getHost(), $path, $context->getBaseUrl());
    }

    private function verifySsl()
    {
        if (isset($this->requestOptions['verify']) && !$this->requestOptions['verify']) {
            return false;
        }

        return true;
    }
    private function log($message)
    {
        $this->logFile->fwrite($message.PHP_EOL);
    }

    public function getLogFile()
    {
        return $this->logFile;
    }

    public function closeLogFile()
    {
        $this->logFile = null;
    }


    private function setLogFilePath()
    {
        $now = new \DateTime();
        $logFilePath = sprintf($this->getContainer()->getParameter('export_path').'/'.
            $this->logFileTemplate, $this->collection->getInstitution()->getInstitutioncode(),
            $this->collection->getCollectioncode(), $now->format('d-m-Y-H-i-s'));

        $this->logFile = new \SplFileObject($logFilePath, 'w+');
    }


}
