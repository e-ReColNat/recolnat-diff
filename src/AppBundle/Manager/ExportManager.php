<?php

namespace AppBundle\Manager;

use AppBundle\Business\DiffHandler;
use AppBundle\Business\Exporter\AbstractExporter;
use AppBundle\Business\Exporter\CsvExporter;
use AppBundle\Business\Exporter\DwcExporter;
use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Business\SelectedSpecimensHandler;
use AppBundle\Business\SessionHandler;
use AppBundle\Business\User\User;
use AppBundle\Entity\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Description of ExportManager
 *
 * @author tpateffoz
 */
class ExportManager
{

    /** @var Session */
    public $sessionManager;

    /** @var $genericEntityManager GenericEntityManager */
    private $genericEntityManager;
    /**
     * @var string
     */
    private $collectionCode = null;

    /**
     * @var integer
     */
    protected $maxItemPerPage;

    /** @var $user \AppBundle\Business\User\User */
    protected $user;

    /** @var \AppBundle\Business\DiffHandler */
    public $diffHandler;

    /** @var  SelectedSpecimensHandler */
    private $selectedSpecimensHandler;

    /** @var  ExportPrefs */
    protected $exportPrefs;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;
    /**
     * @var DiffComputer
     */
    protected $diffComputer;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var SessionHandler
     */
    public $sessionHandler;
    protected $userGroup;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ManagerRegistry      $managerRegistry
     * @param Session              $sessionManager
     * @param GenericEntityManager $genericEntityManager
     * @param int                  $maxItemPerPage
     * @param DiffComputer         $diffComputer
     * @param string               $userGroup
     * @param Logger $logger
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        Session $sessionManager,
        GenericEntityManager $genericEntityManager,
        $maxItemPerPage,
        DiffComputer $diffComputer,
        $userGroup,
        Logger $logger
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->sessionManager = $sessionManager;
        $this->genericEntityManager = $genericEntityManager;
        $this->maxItemPerPage = $maxItemPerPage;
        $this->diffComputer = $diffComputer;
        $this->userGroup = $userGroup;
        $this->logger = $logger;
    }

    /**
     * @param User $user
     * @return $this
     * @throws \Exception
     */
    public function init(User $user)
    {
        $this->user = $user;

        return $this;
    }


    /**
     * @param Collection $collection
     * @return $this
     * @throws \Exception
     */
    public function setCollection(Collection $collection)
    {
        $this->collection = $collection;
        $this->collectionCode = $collection->getCollectioncode();

        if (is_null($this->collection)) {
            throw new \Exception('Can\'t found the collection with collectionCode = '.$this->collectionCode);
        } else {
            $this->diffHandler = new DiffHandler($this->user->getDataDirPath(), $this->collection, $this->userGroup);

            //if (!$this->getDiffHandler()->shouldSearchDiffs()) {
            $this->selectedSpecimensHandler = new SelectedSpecimensHandler($this->diffHandler->getCollectionPath(),
                $this->userGroup);
            $data = $this->getDiffHandler()->getDiffsFile()->getData();
            $data['selectedSpecimens'] = $this->selectedSpecimensHandler->getData();
            $this->sessionHandler = new SessionHandler($this->sessionManager, $this->genericEntityManager, $data);
            $this->getSessionHandler()->init($this->getDiffHandler(), $this->collectionCode);
            //}
        }

        return $this;
    }

    /**
     * @return DiffHandler
     * @throws \Exception
     */
    public function getDiffHandler()
    {
        if ($this->diffHandler instanceof DiffHandler) {
            return $this->diffHandler;
        }
        throw new \Exception('DiffHandler has not been initialized');
    }

    /**
     * @return SelectedSpecimensHandler
     */
    public function getSelectedSpecimenHandler()
    {
        return $this->selectedSpecimensHandler;
    }

    /**
     * @param Request|null      $request
     * @param array|string|null $selectedClassName
     * @param array             $specimensWithChoices
     * @param array             $choicesToRemove
     * @return array
     */
    public function getDiffs(
        Request $request = null,
        $selectedClassName = null,
        $specimensWithChoices = [],
        $choicesToRemove = []
    ) {
        $classesName = [];
        if (is_string($selectedClassName) && $selectedClassName != 'all') {
            $classesName = [$selectedClassName];
        }
        if (is_array($selectedClassName)) {
            $classesName = $selectedClassName;
        }

        if (!is_null($request) && !is_null($request->query->get('reset', null))) {
            $this->sessionManager->clear();
        }
        $allDiffs = $this->sessionManager->get('diffs');
        $diffs = $this->diffHandler->getDiffsFile()->filterResults($allDiffs, $classesName, $specimensWithChoices,
            $choicesToRemove);
        $diffs['selectedSpecimens'] = $this->sessionManager->get('selectedSpecimens', []);

        return self::orderDiffsByTaxon($diffs);
    }

    /**
     * Tri les différences par ordre alphabétique
     * @param array $diffs
     * @return array
     */
    public function orderDiffsByTaxon(array $diffs)
    {
        $sortedDiffs = $diffs;
        if (count($diffs['datas'])) {
            $datas = $diffs['datas'];

            $taxons = $this->getDiffHandler()->getTaxons(array_keys($diffs['datas']));

            array_multisort($taxons, SORT_ASC, SORT_NATURAL|SORT_FLAG_CASE, $datas);
            $sortedDiffs['datas'] = $datas;
        }

        return $sortedDiffs;
    }


    /**
     * @param $catalogNumbers
     * @return array
     */
    public function getDiffsByCatalogNumbers($catalogNumbers)
    {
        $allDiffs = $this->sessionManager->get('diffs');
        $diffs = $this->diffHandler->getDiffsFile()->filterByCatalogNumbers($allDiffs, $catalogNumbers);

        return $diffs;
    }

    /**
     * @param Request $request
     * @return int
     */
    public function getMaxItemPerPage(Request $request)
    {
        $session = $this->sessionManager;

        $requestMaxItem = $request->get('maxItemPerPage', null);
        if (!is_null($requestMaxItem)) {
            $session->set('maxItemPerPage', (int) $requestMaxItem);
        } elseif (!$session->has('maxItemPerPage')) {
            $session->set('maxItemPerPage', $this->maxItemPerPage);
        }

        return $session->get('maxItemPerPage');
    }

    /**
     * @return array
     */
    public function getDiffHandlers()
    {
        $returnDirs = [];
        $dataDirPath = $this->user->getDataDirPath();
        if ($handle = opendir($dataDirPath)) {
            $institutionRepository = $this->managerRegistry->getManager()->getRepository('AppBundle:Institution');
            $collectionRepository = $this->managerRegistry->getManager()->getRepository('AppBundle:Collection');
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..' && is_dir($dataDirPath.$entry)) {
                    $institutionCode = $entry;
                    $institution = $institutionRepository->findOneBy(['institutioncode' => $institutionCode]);
                    if (!is_null($institution)) {
                        $institutionPath = $dataDirPath.$institutionCode;
                        $handle2 = opendir($institutionPath);
                        while (false !== ($subEntry = readdir($handle2))) {
                            if ($subEntry != '.' && $subEntry != '..' && is_dir($institutionPath.'/'.$subEntry)) {
                                $collection = $collectionRepository->findOneBy([
                                    'collectioncode' => $subEntry,
                                    'institution' => $institution
                                ]);
                                if (!is_null($collection)) {
                                    $diffHandler = new DiffHandler($dataDirPath, $collection, $this->userGroup);
                                    $returnDirs[$subEntry] = $diffHandler;
                                }
                            }
                        }
                        closedir($handle2);
                    }
                }
            }
            closedir($handle);
        }

        return $returnDirs;
    }

    /**
     *
     * @return String
     */
    public function getExportDirPath()
    {
        return $this->getDiffHandler()->getCollectionPath().'/export/';
    }

    /**
     *
     * @param array $choices
     */
    public function setChoices($choices)
    {
        foreach ($choices as $row) {
            $this->setChoice($row);
        }
    }

    /**
     *
     * @param array $row
     */
    public function setChoice($row)
    {
        $sessionChoices = [];
        if ($this->sessionManager->has('choices')) {
            $sessionChoices = $this->sessionManager->get('choices');
        }
        $row['data'] = $this->genericEntityManager->getData($row['choice'], $row['className'], $row['fieldName'],
            $row['relationId']);

        $sessionChoices[self::getIndexChoice($row)] = $row;

        $this->sessionManager->set('choices', $sessionChoices);
        $this->diffHandler->getChoicesFile()->save($sessionChoices);
    }

    private static function getIndexChoice(array $choice)
    {
        return md5($choice['className'].$choice['fieldName'].$choice['relationId']);
    }

    /**
     * @return SessionHandler
     */
    public function getSessionHandler()
    {
        return $this->sessionHandler;
    }

    /**
     * Rajoute les nouveaux enregistrements de specimens complets aux données avant export
     * @param array $data
     * @return array
     */
    private function addLonesomesRecords($data)
    {
        // ajout des nouveaux enregistrements de specimens complets
        // Un seul côté
        if ($this->exportPrefs->getSideForNewRecords() != 'both') {
            $dataNewRecords = $this->getSpecimenForLonesomeRecords($this->exportPrefs->getSideForNewRecords());
            $data = array_merge($data, $dataNewRecords);
        } // des deux côtés
        else {
            $dataNewRecords = $this->getSpecimenForLonesomeRecords('recolnat');
            $data = array_merge($data, $dataNewRecords);

            $dataNewRecords = $this->getSpecimenForLonesomeRecords('institution');
            $data = array_merge($data, $dataNewRecords);
        }
        //$data = $this->filterDataByCatalogNumbers($data);

        return $data;
    }

    /**
     * @param string $side
     * @return array
     */
    private function getSpecimenForLonesomeRecords($side)
    {
        $dataNewRecords = [];
        $lonesomeRecords = $this->diffHandler->getLonesomeRecordsFile()
            ->getLonesomeRecordsByBase($side);

        $debut=microtime(true);

        if (count($lonesomeRecords)) {
            $catalogNumbersLonesomeRecords = array_keys($lonesomeRecords);
            $dataNewRecords = $this->genericEntityManager->getEntitiesLinkedToSpecimens(
                $this->exportPrefs->getSideForNewRecords(), $this->collection, $catalogNumbersLonesomeRecords, true);
        }

        foreach ($dataNewRecords as $catalogNumber => $specimen) {

            $arraySpecimenWithEntities = $this->genericEntityManager->formatArraySpecimenForExport($specimen);
            $dataNewRecords[$catalogNumber] = $arraySpecimenWithEntities;
        }
        $this->logger->addDebug(sprintf('%d records : fin : %s', count($lonesomeRecords), microtime(true) -$debut));
        return $dataNewRecords;
    }

    /**
     * Dédoublonne les spécimens avant export
     * @param $data
     * @return array
     */
    private function filterDataByCatalogNumbers($data)
    {
        $filteredCatalogNumbersLonesomeRecords = [];
        if (count($data)) {
            foreach ($data as $index => $specimen) {
                $filteredCatalogNumbersLonesomeRecords[$specimen['catalognumber']] = $specimen;
            }
        }

        return $filteredCatalogNumbersLonesomeRecords;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getArrayDatasWithChoices($data)
    {
        $dataWithChoices = [];
        $entitiesNameWithArray = [
            'Determination',
            'Taxon',
            'Multimedia',
            'Bibliography',
        ];

        foreach ($data as $catalogNumber => $specimen) {

            $arraySpecimenWithEntities = $this->genericEntityManager->formatArraySpecimenForExport($specimen);
            $dataWithChoices[$catalogNumber] = $arraySpecimenWithEntities;

            foreach ($arraySpecimenWithEntities as $className => $row) {
                if (in_array($className, $entitiesNameWithArray)) {
                    foreach ($row as $indexSubArray => $record) {
                        $dataWithChoices[$catalogNumber][$className][$indexSubArray] = $record;
                        $this->getSessionHandler()->setChoiceForEntity($dataWithChoices, $catalogNumber, $className,
                            $record,
                            $indexSubArray);
                    }
                } else {
                    if (!empty($row)) {
                        $dataWithChoices[$catalogNumber][$className] = $row;
                        $this->getSessionHandler()->setChoiceForEntity($dataWithChoices, $catalogNumber, $className,
                            $row);
                    }

                }
            }
        }

        return $dataWithChoices;
    }

    /**
     * @param string      $type
     * @param ExportPrefs $exportPrefs
     * @return \ArrayObject|string
     * @throws \Exception
     */
    public function export($formattedDatas, $type, ExportPrefs $exportPrefs)
    {
        $exporter = null;
        $debut=microtime(true);
        //$datasWithChoices = $this->prepareExport($exportPrefs);
        $this->logger->addDebug(sprintf('fin prepa export : %s', microtime(true) -$debut));
        $debut=microtime(true);
        switch (strtolower($type)) {
            case 'dwc':
                $exporter = new DwcExporter($formattedDatas, $this->getExportDirPath(), $this->userGroup);
                break;
            case 'csv':
                $exporter = new CsvExporter($formattedDatas, $this->getExportDirPath(), $this->userGroup);
        }
        $this->logger->addDebug(sprintf('instanciation export : %s', microtime(true) -$debut));
        $debut=microtime(true);
        if ($exporter instanceof AbstractExporter) {
            $return = $exporter->generate($this->user->getPrefs());
        } else {
            throw new \Exception(sprintf('exporter %s has not been found', $type));
        }
        $this->logger->addDebug(sprintf('creation export : %s', microtime(true) -$debut));
        return $return;
    }

    /**
     * @param ExportPrefs $exportPrefs
     * @return array
     */
    private function prepareExport(ExportPrefs $exportPrefs)
    {
        $this->exportPrefs = $exportPrefs;
        $catalogNumbers = $this->getDiffCatalogNumbers();

        $datas = $this->genericEntityManager
            ->getEntitiesLinkedToSpecimens(
                $this->exportPrefs->getSideForChoicesNotSet(),
                $this->collection,
                $catalogNumbers, true);

        $datasWithChoices = $this->getArrayDatasWithChoices($datas);

        $datasWithChoices = $this->addLonesomesRecords($datasWithChoices);

        return $datasWithChoices;
    }

    /**
     * @return mixed
     */
    public function getDiffCatalogNumbers()
    {
        return $this->sessionManager->get('catalogNumbers');
    }

}
