<?php

namespace AppBundle\Command;


use AppBundle\Business\DiffHandler;
use AppBundle\Business\User\User;
use AppBundle\Manager\DiffComputer;
use AppBundle\Manager\NewDiffManager;
use AppBundle\Manager\UtilityService;
use epierce\CasRestClient;
use Jack\Symfony\ProcessManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SearchDiffCommand extends ContainerAwareCommand
{
    private $serverLoginUrl;
    private $serverTicket;
    private $requestOptions;
    private $apiRecolnatBaseUri;
    private $collectionCode;
    private $apiRecolnatUserPath;
    private $translator;

    /**
     * @var \DateTime
     */
    private $startDate;

    public function __construct(
        $serverLoginUrl,
        $serverTicket,
        $requestOptions,
        $apiRecolnatBaseUri,
        $apiRecolnatUserPath
    ) {
        $this->serverLoginUrl = $serverLoginUrl;
        $this->serverTicket = $serverTicket;
        $this->requestOptions = $requestOptions;
        $this->apiRecolnatBaseUri = $apiRecolnatBaseUri;
        $this->apiRecolnatUserPath = $apiRecolnatUserPath;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('diff:search')
            ->setDescription('Search diffs between buffer and the reference database of e-ReColNat')
            ->addArgument(
                'startDate',
                InputArgument::REQUIRED,
                'Start Date ?'
            )
            ->addArgument(
                'collectionCode',
                InputArgument::REQUIRED,
                'collection code ?'
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
                'cookieTGC for cas Authentification'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->translator = $this->getContainer()->get('translator');
        $this->collectionCode = $input->getArgument('collectionCode');
        $translator = $this->getContainer()->get('translator');

        if (UtilityService::isDateWellFormatted($input->getArgument('startDate'))) {
            $this->startDate = \DateTime::createFromFormat('d/m/Y', $input->getArgument('startDate'));
        } else {
            throw new \Exception($translator->trans('access.denied.wrongDateFormat', [], 'exceptions'));
        }

        $user = $this->getUser($input);
        $collection = $this->getContainer()->get('utility')->getCollection($this->collectionCode);

        $diffHandler = new DiffHandler($user->getDataDirPath(), $collection,
            $this->getContainer()->getParameter('user_group'));

        $diffManager = $this->getContainer()->get('diff.newmanager');
        $diffManager->setCollectionCode($this->collectionCode);
        $diffManager->setStartDate($this->startDate);
        $diffManager->harvestDiffs();

        $diffComputer = $this->getContainer()->get('diff.computer');
        $diffComputer->setCollection($collection);


        $catalogNumbersFiles = $this->createCatalogNumbersFiles($diffManager, $diffHandler);

        $this->lauchDiffProcesses($diffManager, $diffHandler);

        $datas = $this->mergeFiles($diffManager::ENTITIES_NAME, $diffHandler->getCollectionPath());

        $diffHandler->saveDiffs($datas);

        $this->removeCatalogNumbersFiles($catalogNumbersFiles);
    }

    private function removeCatalogNumbersFiles(array $catalogNumbersFiles)
    {
        foreach ($catalogNumbersFiles as $catalogNumbersFile) {
            if (is_file($catalogNumbersFile)) {
                unlink($catalogNumbersFile);
            }
        }
    }

    private function mergeFiles(array $entityNames, $path)
    {
        $mergeData = [];
        foreach ($entityNames as $entityName) {
            $pathName = $path.'/'.$entityName.'.json';
            $datas = json_decode(file_get_contents($pathName), true);
            unlink($pathName);
            $mergeData = $this->arrayMergeRecursiveDistinct($mergeData, $datas);
        }
        $this->filterLonesomesRecords($mergeData['lonesomeRecords'], $mergeData['statsLonesomeRecords']);

        return $mergeData;
    }

    public function filterLonesomesRecords(array &$lonesomesRecords, array &$statsLonesomeRecords)
    {
        $specimens = $lonesomesRecords['Specimen'];
        $catalogNumbersSpecimen = ['recolnat' => [], 'institution' => []];
        $catalogNumbersSpecimen['recolnat'] = array_column($specimens['recolnat'], 'catalogNumber');
        $catalogNumbersSpecimen['institution'] = array_column($specimens['institution'], 'catalogNumber');


        foreach ($lonesomesRecords as $entityName => $records) {
            if ($entityName !== 'Specimen') {
                if (count($records['recolnat'])) {
                    foreach ($records['recolnat'] as $key => $record) {
                        if (in_array($record['catalogNumber'], $catalogNumbersSpecimen['recolnat'])) {
                            unset($lonesomesRecords[$entityName]['recolnat'][$key]);
                        }
                    }
                }
                if (count($records['institution'])) {
                    foreach ($records['institution'] as $key => $record) {
                        if (in_array($record['catalogNumber'], $catalogNumbersSpecimen['institution'])) {
                            unset($lonesomesRecords[$entityName]['institution'][$key]);
                        }
                    }
                }
            }
        }
        $statsLonesomeRecords = DiffComputer::computeStatsLonesomeRecords($lonesomesRecords);
    }


    private function getComputeProcess(NewDiffManager $diffManager, $savePath)
    {
        $processes = [];
        foreach ($diffManager::ENTITIES_NAME as $entityName) {
            $consoleDir = realpath('/'.$this->getContainer()->get('kernel')->getRootDir().'/../bin/console');
            $command = sprintf('%s diff:compute -vvv %s %s %s',
                $consoleDir, $this->collectionCode, $entityName, $savePath);

            $process = new Process($command);
            $process->setTimeout(null);
            $processes[] = $process;
        }

        return $processes;
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
            throw new AccessDeniedException($this->translator->trans('access.denied.wrongPermission', [],
                'exceptions'));
        }
        $user->init($this->getContainer()->getParameter('export_path'));

        return $user;
    }

    /**
     * @param InputInterface $input
     * @return User
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
            if (
            !$user->checkServiceTicket(
                $cookieTGC,
                $this->getContainer()->getParameter('server_login_url'),
                $this->getContainer()->getParameter('api_recolnat_server_ticket_path'),
                $this->getContainer()->getParameter('api_recolnat_auth_service_url'),
                $verifySsl)
            ) {
                throw new AccessDeniedException($this->translator->trans('access.denied.wrongPermission', [],
                    'exceptions'));
            }
        } else {
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
            $response = $client->post('https://localhost/recolnat-diff/search');
            if ($response->getStatusCode() == 200) {
                $user = new User($username, $this->apiRecolnatBaseUri, $this->apiRecolnatUserPath,
                    $this->getContainer()->getParameter('user_group'));

                return $user;
            } else {
                throw new AccessDeniedException($this->getContainer()->get('translator')
                    ->trans('access.denied.wrongPassword', [], 'exceptions'));
            }
        } else {
            throw new AccessDeniedException($this->getContainer()->get('translator')
                ->trans('access.denied.wrongPassword', [], 'exceptions'));
        }
    }

    private function verifySsl()
    {
        if (isset($this->requestOptions['verify']) && !$this->requestOptions['verify']) {
            return false;
        }

        return true;
    }

    /**
     * @param NewDiffManager $diffManager
     * @param DiffHandler    $diffHandler
     * @return array
     */
    protected function createCatalogNumbersFiles(NewDiffManager $diffManager, DiffHandler $diffHandler)
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

    /**
     * @param NewDiffManager  $diffManager
     * @param DiffHandler     $diffHandler
     */
    protected function lauchDiffProcesses(
        NewDiffManager $diffManager,
        DiffHandler $diffHandler
    ) {
        $processes = $this->getComputeProcess($diffManager, $diffHandler->getCollectionPath());

        $processManager = new ProcessManager();
        $max_parallel_processes = 8;
        $polling_interval = 1000; // microseconds
        $processManager->runParallel($processes, $max_parallel_processes, $polling_interval);
    }


    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
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
}
