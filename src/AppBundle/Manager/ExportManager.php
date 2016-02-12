<?php

namespace AppBundle\Manager;

use AppBundle\Business\Exporter\ExportPrefs;
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

    private $exportPath;
    public $sessionManager;
    private $institutionCode;

    /** @var $genericEntityManager GenericEntityManager */
    private $genericEntityManager;
    private $collectionCode = null;

    /** @var \AppBundle\Manager\DiffManager */
    protected $diffManager;
    protected $maxItemPerPage;

    /** @var $user \AppBundle\Business\User\User */
    protected $user;

    /** @var \AppBundle\Business\DiffHandler */
    private $diffHandler;

    /** @var  ExportPrefs */
    protected $exportPrefs;

    /**
     * 
     * @param string $export_path
     * @param Session $sessionManager
     * @param GenericEntityManager $genericEntityManager
     * @param \AppBundle\Manager\DiffManager
     */
    public function __construct($export_path, Session $sessionManager, GenericEntityManager $genericEntityManager, DiffManager $diffManager, $maxItemPerPage)
    {
        $this->exportPath = $export_path;
        $this->sessionManager = $sessionManager;
        $this->genericEntityManager = $genericEntityManager;
        $this->diffManager = $diffManager;
        $this->maxItemPerPage = $maxItemPerPage;
    }

    /**
     * @param string $institutionCode
     * @param string $collectionCode
     * @return $this
     */
    public function init($institutionCode, $collectionCode)
    {
        $this->institutionCode = $institutionCode;
        $this->collectionCode = $collectionCode;
        $this->user = new User($this->exportPath, $this->maxItemPerPage);
        $this->user->init($this->institutionCode);

        if (!is_null($collectionCode)) {
            $this->collectionCode = $collectionCode;
            $fs = new \Symfony\Component\Filesystem\Filesystem();

            if (!$fs->exists($this->getExportDirPath())) {
                $fs->mkdir($this->getExportDirPath(), 0755);
            }
            chmod($this->getExportDirPath(), 0777);

            $this->diffHandler = new DiffHandler($this->user->getDataDirPath(), $this->collectionCode);

            $doReload = false;
            $path = $this->diffHandler->getChoices()->getPathname();
            if ($this->diffHandler->getDiffs()->generateDiff) {
                $results = $this->launchDiffProcess();
            } else {
                $results = $this->diffHandler->getDiffs()->getData();
            }
            $stats = $results['stats'];
            unset($results['stats']);
            $this->sessionManager->set('diffs', $results);
            $this->sessionManager->set('stats', $stats);
            $this->sessionManager->set('specimensCode', $this->getSpecimensCode());


            if (!($this->sessionManager->has('file') || $this->sessionManager->get('file') != $this->collectionCode)) {
                $doReload = true;
            }
            if (!($this->sessionManager->has('choices') ) || empty($this->sessionManager->get('choices'))) {
                $doReload = true;
            }
            if ($doReload && $fs->exists($path)) {
                $this->sessionManager->set('choices', $this->diffHandler->getChoices()->getContent());
            } else {
                $this->sessionManager->set('file', $this->collectionCode);
            }
        }
        return $this;
    }

    /**
     * @param string $db
     * @param mixed $selectedClassesNames
     * @return array
     */
    public function getLonesomeRecords($db=null, $selectedClassesNames=null)
    {
        $classesName = [];
        if (!is_null($selectedClassesNames) && is_string($selectedClassesNames) && $selectedClassesNames != 'all') {
            $classesName = [$selectedClassesNames];
            array_walk($classesName, function (&$className) {
                $className = ucfirst(strtolower($className));
            });
        }

        $lonesomeRecords = $this->diffHandler->getDiffs()->getData()['lonesomeRecords'];
        $returnLonesomes=[];
        if (!is_null($db)) {
            foreach ($lonesomeRecords as $className => $items) {
                if (!empty($classesName)) {
                    if (in_array($className, $classesName)) {
                        $returnLonesomes[$className][$db] = $lonesomeRecords[$className][$db];
                    }
                }
                else {
                    $returnLonesomes[$className][$db] = $lonesomeRecords[$className][$db];
                }
            }
        }
        else {
            if (empty($classesName)) {
                $returnLonesomes = $lonesomeRecords;
            } else {
                foreach ($classesName as $className) {
                    $returnLonesomes[$className] = $lonesomeRecords[$className];
                }
            }
        }
        return $returnLonesomes;
    }

    /**
     * @param $db
     * @param null $selectedClassesNames
     * @return array
     */
    public function getLonesomeRecordsBySpecimenCode($db, $selectedClassesNames=null)
    {
        $lonesomeRecordsBySpecimenCodes=[] ;
        $specimenLonesomeRecords = $this->getLonesomeRecords($db, 'specimen');
        $refSpecimenCode = array_column($specimenLonesomeRecords['Specimen'][$db], 'specimenCode') ;
        $fullLonesomeRecords=$this->getLonesomeRecords($db, $selectedClassesNames) ;

        if (!empty($fullLonesomeRecords)) {
            foreach ($fullLonesomeRecords as $className => $lonesomeRecords) {
                foreach ($lonesomeRecords[$db] as $item) {
                    // Si le specimencode de l'enregistrement est dans la liste des specimens de ref c'est que tous les
                    // enregistrements correspondant à ce specimen code sont nouveaux
                    // puisque le specimen n'est pas dans l'autre base
                    if (!in_array($item['specimenCode'], $refSpecimenCode) || $selectedClassesNames=='specimen') {
                        $lonesomeRecordsBySpecimenCodes[$item['specimenCode']][] = [
                            'className' => $className,
                            'id' => $item['id']
                        ];
                    }
                    elseif($selectedClassesNames=='all') {
                        $lonesomeRecordsBySpecimenCodes[$item['specimenCode']][] = [
                            'className' => $className,
                            'id' => $item['id']
                        ];
                    }

                }
            }
        }

        return $lonesomeRecordsBySpecimenCodes;
    }


    /**
     * @return array
     */
    public function launchDiffProcess()
    {
        $results = $this->diffManager->init($this->institutionCode, $this->collectionCode);
        $this->diffHandler->getDiffs()->save($results);
        $this->diffHandler->getDiffs()->generateDiff = false;
        return $results;
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
     * @param Request|null $request
     * @param array|string|null $selectedClassName
     * @param array $specimensWithChoices
     * @param array $choicesToRemove
     * @return array
     */
    public function getDiffs(Request $request = null, $selectedClassName = null, $specimensWithChoices = [], $choicesToRemove = [])
    {
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
        $diffs = $this->diffHandler->getDiffs()->filterResults($allDiffs, $classesName, $specimensWithChoices, $choicesToRemove);
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
     * @return array
     */
    public function getSpecimensCode()
    {
        $stats = $this->sessionManager->get('diffs');
        return array_keys($stats['datas']);
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
                if ($entry != "." && $entry != ".." && is_dir($institutionDir . $entry)) {
                    $returnDirs[] = new \AppBundle\Business\DiffHandler($institutionDir, $entry);
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
        return $this->user->getDataDirPath() . $this->collectionCode . '/export/';
    }

    /**
     * 
     * @return String
     */
    public function getChoicesFileName()
    {
        if (!is_null($this->collectionCode)) {
            return $this->diffHandler->getChoices()->getPathname();
        }
        return null;
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
        $row['data'] = $this->genericEntityManager->getData($row['choice'], $row['className'], $row['fieldName'], $row['relationId']);
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
     * 
     * @return array
     */
    public function getChoices()
    {
        if ($this->sessionManager->has('choices')) {
            return $this->sessionManager->get('choices');
        }
        return [];
    }

    /**
     * @return array
     */
    public function getChoicesForDisplay()
    {
        $choices = $this->getChoices();
        $returnChoices = [];
        if (count($choices) > 0) {
            foreach ($choices as $choice) {
                if (!isset($returnChoices[$choice['className']])) {
                    $returnChoices[$choice['className']] = [];
                }
                if (!isset($returnChoices[$choice['className']][$choice['relationId']])) {
                    $returnChoices[$choice['className']][$choice['relationId']] = [];
                }
                $returnChoices[$choice['className']][$choice['relationId']][$choice['fieldName']] = $choice['choice'];
            }
        }
        return $returnChoices;
    }

    /**
     * @return array
     */
    public function getChoicesBySpecimenCode()
    {
        $choices = $this->getChoices();
        $returnChoices = array();
        if (count($choices) > 0) {
            foreach ($choices as $choice) {
                if (!isset($returnChoices[$choice['specimenCode']])) {
                    $returnChoices[$choice['specimenCode']] = [];
                }
                unset($choice[$choice['specimenCode']]);
                $returnChoices[$choice['specimenCode']][] = $choice;
            }
        }
        return $returnChoices;
    }

    /**
     * @return void
     */
    public function saveChoices()
    {
        $this->diffHandler->getChoices()->save($this->getChoices());
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
        foreach ($datas as $index => $specimen) {
            $arraySpecimenWithEntities = $genericEntityManager->formatArraySpecimen($specimen);
            $datasWithChoices[$index] = $arraySpecimenWithEntities;
            foreach ($arraySpecimenWithEntities as $className => $row) {
                if (in_array($className, $entitiesNameWithArray)) {
                    foreach ($row as $indexSubArray => $record) {
                        foreach ($record as $fieldName => $value) {
                            $datasWithChoices[$index][$className][$indexSubArray][$fieldName] = $value;
                        }
                        $this->setChoiceForEntity($datasWithChoices, $index, $className, $record, $indexSubArray);
                    }
                } else {
                    if (!empty($row)) {
                        foreach ($row as $fieldName => $value) {
                            $datasWithChoices[$index][$className][$fieldName] = $value;
                        }
                        $this->setChoiceForEntity($datasWithChoices, $index, $className, $row);
                    }
                }
            }
        }
        return $datasWithChoices;
    }

    /**
     * @param array $datasWithChoices
     * @param string $index
     * @param string $className
     * @param array $arrayEntity
     * @param null|string $indexSubArray
     */
    private function setChoiceForEntity(&$datasWithChoices, $index, $className, $arrayEntity, $indexSubArray = null)
    {
        $choices = $this->getChoicesForEntity($className, $arrayEntity);
        if (is_array($choices) && count($choices) > 0) {
            foreach ($choices as $choice) {
                if (!is_null($indexSubArray)) {
                    $datasWithChoices[$index][$className][$indexSubArray][$choice['fieldName']] = $choice['data'];
                } else {
                    $datasWithChoices[$index][$className][$choice['fieldName']] = $choice['data'];
                }
            }
        }
    }

    /**
     *
     * @param string $className
     * @param array $arrayEntity
     * @return array
     */
    public function getChoicesForEntity($className, $arrayEntity)
    {
        $returnChoices = null;
        $relationId = null;
        if (array_key_exists($this->genericEntityManager->getIdentifierName($className), $arrayEntity)) {
            $relationId = $arrayEntity[$this->genericEntityManager->getIdentifierName($className)];

            foreach ($this->getChoices() as $row) {
                if ($row['className'] == $className && $row['relationId'] == $relationId) {
                    $returnChoices[] = $row;
                }
            }
        }
        return $returnChoices;
    }

    /**
     * @param ExportPrefs $exportPrefs
     * @return string
     */
    public function getCsv(ExportPrefs $exportPrefs)
    {
        $this->exportPrefs = $exportPrefs;
        $specimenCodes = $this->sessionManager->get('specimensCode');
        $datas = $this->genericEntityManager->getEntitiesLinkedToSpecimens($exportPrefs->getSideForChoicesNotSet(), $specimenCodes);
        $datasWithChoices = $this->getArrayDatasWithChoices($datas);
        $csvExporter = new CsvExporter(
                $datasWithChoices, $this->getExportDirPath(), $exportPrefs);

        return $csvExporter->generate($this->user->getPrefs());
    }

    /**
     * @param ExportPrefs $exportPrefs
     * @return string
     */
    public function getDwc(ExportPrefs $exportPrefs)
    {
        $this->exportPrefs = $exportPrefs;
        $specimenCodes = $this->sessionManager->get('specimensCode');
        $datas = $this->genericEntityManager->getEntitiesLinkedToSpecimens($exportPrefs->getSideForChoicesNotSet(), $specimenCodes);
        $datasWithChoices = $this->getArrayDatasWithChoices($datas);
        $dwcExporter = new DwcExporter(
                $datasWithChoices, $this->getExportDirPath(), $exportPrefs);

        return $dwcExporter->generate($this->user->getPrefs());
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
        foreach ($this->getChoices() as $row) {
            if ($row['className'] == $className && $row['relationId'] == $relationId && $row['fieldName'] == $fieldName) {
                $returnChoice = $row['data'];
            }
        }
        return $returnChoice;
    }

}
