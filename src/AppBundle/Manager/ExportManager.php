<?php

namespace AppBundle\Manager;

use AppBundle\Business\Exporter\AbstractExporter;
use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Business\SelectedSpecimensHandler;
use AppBundle\Business\SessionHandler;
use AppBundle\Entity\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Business\DiffHandler;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Business\User\User;
use AppBundle\Business\Exporter\DwcExporter;
use AppBundle\Business\Exporter\CsvExporter;

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
    private $diffHandler;

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
     * @param ManagerRegistry      $managerRegistry
     * @param Session              $sessionManager
     * @param GenericEntityManager $genericEntityManager
     * @param int                  $maxItemPerPage
     * @param DiffComputer         $diffComputer
     * @param string               $userGroup
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        Session $sessionManager,
        GenericEntityManager $genericEntityManager,
        $maxItemPerPage,
        DiffComputer $diffComputer,
        $userGroup
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->sessionManager = $sessionManager;
        $this->genericEntityManager = $genericEntityManager;
        $this->maxItemPerPage = $maxItemPerPage;
        $this->diffComputer = $diffComputer;
        $this->userGroup = $userGroup;
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
     * @param string $collectionCode
     * @return $this
     * @throws \Exception
     */
    public function setCollectionCode($collectionCode)
    {
        $this->collectionCode = $collectionCode;
        $this->collection = $this->managerRegistry->getManager('default')
            ->getRepository('AppBundle:Collection')->findOneBy(['collectioncode' => $this->collectionCode]);
        if (is_null($this->collection)) {
            throw new \Exception('Can\'t found the collection with collectionCode = '.$this->collectionCode);
        } else {
            $this->diffHandler = new DiffHandler($this->user->getDataDirPath(), $this->collection, $this->userGroup);

            if (!$this->getDiffHandler()->shouldSearchDiffs()) {
                $this->selectedSpecimensHandler = new SelectedSpecimensHandler($this->diffHandler->getCollectionPath(),
                    $this->userGroup);
                $data = $this->getDiffHandler()->getDiffsFile()->getData();
                $data['selectedSpecimens'] = $this->selectedSpecimensHandler->getData();
                $this->sessionHandler = new SessionHandler($this->sessionManager, $this->genericEntityManager, $data);
                $this->getSessionHandler()->init($this->getDiffHandler(), $this->collectionCode);
            }
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

            $taxons = $this->getDiffHandler()->getTaxons(array_keys($diffs['datas'])) ;

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
            $catalogNumbersLonesomeRecords = array_keys($this->diffHandler->getLonesomeRecordsFile()->getLonesomeRecordsOrderedByCatalogNumbers(
                $this->exportPrefs->getSideForNewRecords()));

            $datasNewRecords = $this->genericEntityManager->getEntitiesLinkedToSpecimens(
                $this->exportPrefs->getSideForNewRecords(),
                $this->collection,
                $catalogNumbersLonesomeRecords);
            $data = array_merge($data, $datasNewRecords);

        } // des deux côtés
        else {
            $catalogNumbersLonesomeRecords = array_keys($this->diffHandler->getLonesomeRecordsFile()->getLonesomeRecordsOrderedByCatalogNumbers(
                'recolnat'));
            $datasNewRecords = $this->genericEntityManager->getEntitiesLinkedToSpecimens('recolnat',
                $this->collection,
                array_keys($catalogNumbersLonesomeRecords));
            $data = array_merge($data, $datasNewRecords);


            $catalogNumbersLonesomeRecords = array_keys($this->diffHandler->getLonesomeRecordsFile()->getLonesomeRecordsOrderedByCatalogNumbers(
                'institution'));
            $datasNewRecords = $this->genericEntityManager->getEntitiesLinkedToSpecimens('institution',
                $this->collection,
                $catalogNumbersLonesomeRecords);
            $data = array_merge($data, $datasNewRecords);

        }
        $data = $this->filterDataByCatalogNumbers($data);

        return $data;
    }

    /**
     * Dédoublonne les spécimens avant export
     * @param $data
     * @return array
     */
    private function filterDataByCatalogNumbers($data)
    {
        $filteredCatalogNumbersLonesomeRecords = [];
        foreach ($data as $index => $specimen) {
            $filteredCatalogNumbersLonesomeRecords[$specimen['catalognumber']] = $specimen;
        }

        return $filteredCatalogNumbersLonesomeRecords;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getArrayDatasWithChoices($data)
    {
        $dataWithChoices = [];
        $entitiesNameWithArray = [
            'Determination',
            'Taxon',
            'Multimedia',
            'Bibliography',
        ];
        $data = $this->addLonesomesRecords($data);

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
    public function export($type, ExportPrefs $exportPrefs)
    {
        $exporter = null;
        $datasWithChoices = $this->prepareExport($exportPrefs);
        switch ($type) {
            case 'dwc':
                $exporter = new DwcExporter($datasWithChoices, $this->getExportDirPath(), $this->userGroup);
                break;
            case 'csv':
                $exporter = new CsvExporter($datasWithChoices, $this->getExportDirPath(), $this->userGroup);
        }
        if ($exporter instanceof AbstractExporter) {
            return $exporter->generate($this->user->getPrefs());
        } else {
            throw new \Exception(sprintf('exporter %s has not been found', $type));
        }
    }

    /**
     * @param ExportPrefs $exportPrefs
     * @return array
     */
    private function prepareExport(ExportPrefs $exportPrefs)
    {
        $this->exportPrefs = $exportPrefs;
        $catalogNumbers = $this->sessionManager->get('catalogNumbers');
        $datas = $this->genericEntityManager->getEntitiesLinkedToSpecimens($this->exportPrefs->getSideForChoicesNotSet(),
            $this->collection, $catalogNumbers);
        $datasWithChoices = $this->getArrayDatasWithChoices($datas);

        return $datasWithChoices;
    }
}
