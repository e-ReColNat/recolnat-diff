<?php

namespace AppBundle\Manager;

use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Business\SessionHandler;
use AppBundle\Entity\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
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

    /**
     * @var string
     */
    private $exportPath;
    /** @var Session */
    public $sessionManager;
    /**
     * @var string
     */
    private $institutionCode;

    /** @var $genericEntityManager GenericEntityManager */
    private $genericEntityManager;
    /**
     * @var string
     */
    private $collectionCode = null;

    /** @var \AppBundle\Manager\DiffManager */
    protected $diffManager;
    /**
     * @var integer
     */
    protected $maxItemPerPage;

    /** @var $user \AppBundle\Business\User\User */
    protected $user;

    /** @var \AppBundle\Business\DiffHandler */
    private $diffHandler;

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

    /**
     * @param ManagerRegistry      $managerRegistry
     * @param string               $export_path
     * @param Session              $sessionManager
     * @param DiffComputer         $diffComputer
     * @param GenericEntityManager $genericEntityManager
     * @param DiffManager          $diffManager
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $export_path,
        Session $sessionManager,
        GenericEntityManager $genericEntityManager,
        DiffManager $diffManager,
        $maxItemPerPage,
        DiffComputer $diffComputer
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->exportPath = $export_path;
        $this->sessionManager = $sessionManager;
        $this->genericEntityManager = $genericEntityManager;
        $this->diffManager = $diffManager;
        $this->maxItemPerPage = $maxItemPerPage;
        $this->diffComputer = $diffComputer;
    }

    /**
     * @param string $institutionCode
     * @param string $collectionCode
     * @return $this
     * @throws \Exception
     */
    public function init($institutionCode, $collectionCode)
    {
        $em = $this->managerRegistry->getManager('default');
        $this->collection = $em->getRepository('AppBundle:Collection')->findOneBy(['collectioncode' => $collectionCode]);

        if (is_null($this->collection)) {
            throw new \Exception('Can\'t found the collection with collectionCode = '.$collectionCode);
        }

        $this->institutionCode = $institutionCode;
        $this->collectionCode = $collectionCode;
        $this->user = new User($this->exportPath, $this->maxItemPerPage);
        $this->user->init($this->institutionCode);

        if (!is_null($collectionCode)) {
            $fs = new Filesystem();

            if (!$fs->exists($this->getExportDirPath())) {
                $fs->mkdir($this->getExportDirPath(), 0755);
            }
            chmod($this->getExportDirPath(), 0777);

            $this->diffHandler = new DiffHandler($this->user->getDataDirPath(), $this->collectionCode);

            $path = $this->diffHandler->getChoices()->getPathname();

            $this->launchDiffProcess();
            $doReload = $this->sessionHandler->mustReload($this->collectionCode);
            if ($doReload && $fs->exists($path)) {
                $this->sessionManager->set('choices', $this->diffHandler->getChoices()->getContent());
            } else {
                $this->sessionManager->set('file', $this->collectionCode);
            }
        }
        return $this;
    }

    /**
     * @return void
     */
    public function launchDiffProcess()
    {
        if ($this->diffHandler->getDiffs()->searchDiffs) {
            $diffs = $this->diffManager->init($this->collection);
            $diffComputer = $this->diffComputer->init($diffs);
            $data = array_merge($diffComputer->getDiffs(),
                [
                    'stats' => $diffComputer->getAllStats(),
                    'lonesomeRecords' => $diffComputer->getLonesomeRecords(),
                    'statsLonesomeRecords' => $diffComputer->getStatsLonesomeRecords()
                ]);
            $this->diffHandler->getDiffs()->save($data);
            $this->diffHandler->getDiffs()->searchDiffs = false;
        } else {
            $data = $this->diffHandler->getDiffs()->getData();
        }
        $this->sessionHandler = new SessionHandler($this->sessionManager, $this->genericEntityManager, $data);
    }

    /**
     *
     * @return DiffHandler
     */
    public function getDiffHandler()
    {
        if ($this->diffHandler instanceof DiffHandler) {
            return $this->diffHandler;
        }
        return null;
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
        $diffs = $this->diffHandler->getDiffs()->filterResults($allDiffs, $classesName, $specimensWithChoices,
            $choicesToRemove);
        return $diffs;
    }

    /**
     * @param $specimensCode
     * @return array
     */
    public function getDiffsBySpecimensCode($specimensCode)
    {
        $allDiffs = $this->sessionManager->get('diffs');
        $diffs = $this->diffHandler->getDiffs()->filterBySpecimensCode($allDiffs, $specimensCode);
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
    public function getFiles()
    {
        $returnDirs = [];
        $institutionDir = $this->user->getDataDirPath();
        if ($handle = opendir($institutionDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && is_dir($institutionDir.$entry)) {
                    $returnDirs[] = new DiffHandler($institutionDir, $entry);
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
        return $this->user->getDataDirPath().$this->collectionCode.'/export/';
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
        $flag = false;
        $row['data'] = $this->genericEntityManager->getData($row['choice'], $row['className'], $row['fieldName'],
            $row['relationId']);
        if (is_array($sessionChoices) && count($sessionChoices) > 0) {
            foreach ($sessionChoices as $key => $choice) {
                if (
                    $choice['className'] == $row['className'] &&
                    $choice['fieldName'] == $row['fieldName'] &&
                    $choice['relationId'] == $row['relationId']
                ) {
                    $sessionChoices[$key] = $row;
                    $flag = true;
                }
            }
        }

        if (!$flag) {
            $sessionChoices[] = $row;
        }
        $this->sessionManager->set('choices', $sessionChoices);
        $this->diffHandler->getChoices()->save($sessionChoices);
    }

    /**
     * @return SessionHandler
     */
    public function getSessionHandler()
    {
        return $this->sessionHandler;
    }

    /**
     * @return void
     */
    public function saveChoices()
    {
        $this->diffHandler->getChoices()->save($this->getSessionHandler()->getChoices());
    }

    /**
     * Rajoute les nouveaux enregistrements de specimens complets aux données avant export
     * @param array $datas
     * @return array
     */
    private function addNewRecords($datas)
    {
        // ajout des nouveaux enregistrements de specimens complets
        // Un seul côté
        if ($this->exportPrefs->getSideForNewRecords() != 'both') {
            $specimenCodesLonesomeRecords = $this->diffHandler->getDiffs()->getLonesomeRecordsOrderedBySpecimenCodes(
                $this->exportPrefs->getSideForNewRecords());
            $datasNewRecords = $this->genericEntityManager->getEntitiesLinkedToSpecimens($this->exportPrefs->getSideForNewRecords(),
                array_keys($specimenCodesLonesomeRecords));
            $datas = array_merge($datas, $datasNewRecords);
            return $datas;
        } // des deux côtés
        else {
            $specimenCodesLonesomeRecords = $this->diffHandler->getDiffs()->getLonesomeRecordsOrderedBySpecimenCodes(
                'recolnat');
            $datasNewRecords = $this->genericEntityManager->getEntitiesLinkedToSpecimens('recolnat',
                array_keys($specimenCodesLonesomeRecords));
            $datas = array_merge($datas, $datasNewRecords);


            $specimenCodesLonesomeRecords = $this->diffHandler->getDiffs()->getLonesomeRecordsOrderedBySpecimenCodes(
                'institution');
            $datasNewRecords = $this->genericEntityManager->getEntitiesLinkedToSpecimens('institution',
                array_keys($specimenCodesLonesomeRecords));
            $datas = array_merge($datas, $datasNewRecords);
            return $datas;
        }
    }

    /**
     * @param array $datas
     * @return array
     */
    private function getArrayDatasWithChoices($datas)
    {
        $genericEntityManager = $this->genericEntityManager;
        $datasWithChoices = [];
        $entitiesNameWithArray = [
            'Determination',
            'Taxon',
            'Multimedia',
            'Bibliography',
        ];
        $datas = $this->addNewRecords($datas);

        foreach ($datas as $index => $specimen) {

            $arraySpecimenWithEntities = $genericEntityManager->formatArraySpecimen($specimen);
            $datasWithChoices[$index] = $arraySpecimenWithEntities;

            foreach ($arraySpecimenWithEntities as $className => $row) {
                if (in_array($className, $entitiesNameWithArray)) {
                    foreach ($row as $indexSubArray => $record) {
                        foreach ($record as $fieldName => $value) {
                            $datasWithChoices[$index][$className][$indexSubArray][$fieldName] = $value;
                        }
                        $this->getSessionHandler()->setChoiceForEntity($datasWithChoices, $index, $className, $record,
                            $indexSubArray);
                    }
                } else {
                    if (!empty($row)) {
                        foreach ($row as $fieldName => $value) {
                            $datasWithChoices[$index][$className][$fieldName] = $value;
                        }
                        $this->getSessionHandler()->setChoiceForEntity($datasWithChoices, $index, $className, $row);
                    }
                }
            }
        }
        return $datasWithChoices;
    }

    /**
     * @param string      $type
     * @param ExportPrefs $exportPrefs
     * @return \ArrayObject|string
     */
    public function export($type, ExportPrefs $exportPrefs)
    {
        $datasWithChoices = $this->prepareExport($exportPrefs);
        switch ($type) {
            case 'dwc' :
                $exporter = new DwcExporter($datasWithChoices, $this->getExportDirPath());
                break;
            case 'csv' :
                $exporter = new CsvExporter($datasWithChoices, $this->getExportDirPath());
        }
        return $exporter->generate($this->user->getPrefs());
    }

    /**
     * @param ExportPrefs $exportPrefs
     * @return array
     */
    private function prepareExport(ExportPrefs $exportPrefs)
    {
        $this->exportPrefs = $exportPrefs;
        $specimenCodes = $this->sessionManager->get('specimensCode');
        $datas = $this->genericEntityManager->getEntitiesLinkedToSpecimens($this->exportPrefs->getSideForChoicesNotSet(),
            $specimenCodes);
        $datasWithChoices = $this->getArrayDatasWithChoices($datas);
        return $datasWithChoices;
    }

    /**
     *
     * @param string $className
     * @param string $relationId
     * @param string $fieldName
     * @return array
     */
    public function getChoice($className, $relationId, $fieldName)
    {
        $returnChoice = null;
        foreach ($this->sessionHandler->getChoices() as $row) {
            if ($row['className'] == $className && $row['relationId'] == $relationId && $row['fieldName'] == $fieldName) {
                $returnChoice = $row['data'];
            }
        }
        return $returnChoice;
    }
}
