<?php

namespace AppBundle\Manager;

use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Manager\GenericEntityManager;
use AppBundle\Business\DiffHandler;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Business\User\User ;

/**
 * Description of ExportManager
 *
 * @author tpateffoz
 */
class ExportManager
{

    private $exportPath;
    private $sessionManager;
    private $institutionCode;

    /** @var $genericEntityManager GenericEntityManager */
    private $genericEntityManager;
    private $filename = null;
    protected $diffManager;
    protected $maxItemPerPage;
    /* @var $user AppBundle\Business\User\User */
    protected $user ;

    /** @var \AppBundle\Business\DiffHandler */
    private $diffHandler;

    private $prefs ;
    /**
     * 
     * @param string $export_path
     * @param Session $sessionManager
     * @param GenericEntityManager $genericEntityManager
     * @param \AppBundle\Manager\DiffManager
     * @return \AppBundle\Manager\ExportManager
     */
    public function __construct($export_path, Session $sessionManager, GenericEntityManager $genericEntityManager, DiffManager $diffManager, $maxItemPerPage)
    {
        $this->exportPath = $export_path;
        $this->sessionManager = $sessionManager;
        $this->genericEntityManager = $genericEntityManager;
        $this->diffManager = $diffManager;
        $this->maxItemPerPage = $maxItemPerPage;
        return $this;
    }

    /**
     * 
     * @param String $institutionCode
     * @return \AppBundle\Manager\ExportManager
     */
    public function init($institutionCode, $filename = null)
    {
        $this->institutionCode = $institutionCode;
        $this->user = new User($this->exportPath, $this->maxItemPerPage) ;
        $this->user->init($institutionCode);

        if (!is_null($filename)) {
            $this->filename = $filename;
            $fs = new \Symfony\Component\Filesystem\Filesystem();

            if (!$fs->exists($this->getExportDirPath())) {
                $fs->mkdir($this->getExportDirPath(), 0755);
            }
            chmod($this->getExportDirPath(), 0777);

            $this->diffHandler = new DiffHandler($this->user->getDataDirPath(), $this->filename);

            $doReload = false;
            $path = $this->diffHandler->getChoices()->getPathname();
            if ($this->diffHandler->getDiffs()->generateDiff) {
                $results = $this->diffManager->init($institutionCode);
                $this->sessionManager->set('diffs', $results['diffs']);
                $this->sessionManager->set('specimensCode', $results['specimensCode']);
                $this->sessionManager->set('stats', $results['stats']);
                $this->diffHandler->getDiffs()->saveDiffs($results['diffs'], $results['stats'], $results['specimensCode']);
                $this->diffHandler->getDiffs()->generateDiff = false;
            } else {
                $results = $this->diffHandler->getDiffs()->getData();
                $this->sessionManager->set('diffs', $results['diffs']);
                $this->sessionManager->set('specimensCode', $results['specimensCode']);
                $this->sessionManager->set('stats', $results['stats']);
            }

            if (!($this->sessionManager->has('file') || $this->sessionManager->get('file') != $filename)) {
                $doReload = true;
            }
            if (!($this->sessionManager->has('choices') ) || empty($this->sessionManager->get('choices'))) {
                $doReload = true;
            }
            if ($doReload && $fs->exists($path)) {
                $this->sessionManager->set('choices', $this->diffHandler->getChoices()->getContent());
            } else {
                $this->sessionManager->set('file', $filename);
            }
        }
        return $this;
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

    public function getSpecimenIdsAndDiffsAndStats(Request $request, $selectedClassName = null, $specimensWithChoices = [], $choicesToRemove = [])
    {
        $session = $this->sessionManager;
        $className = [];
        if (is_string($selectedClassName) && $selectedClassName != 'all') {
            $className = [$selectedClassName];
        }
        if (is_array($selectedClassName)) {
            $className = $selectedClassName;
        }

        if (!is_null($request->query->get('reset', null))) {
            $session->clear();
        }
        $stats = $this->sessionManager->get('stats');
        $stats = $this->diffHandler->getDiffs()->filterResults($stats, $className, $specimensWithChoices, $choicesToRemove);
        $session->set('stats', $stats);
        return [$session->get('specimensCode'), $session->get('diffs'), $stats];
    }

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
     * @param String $institutionCode
     * @return String
     */
    public function getExportDirPath()
    {
        return $this->user->getDataDirPath() . $this->filename . '/export/';
    }

    /**
     * 
     * @param String $institutionCode
     * @return String
     */
    public function getChoicesFileName()
    {
        if (!is_null($this->filename)) {
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
     * @param array $sessionChoices
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
        if (count($sessionChoices) > 0) {
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

    public function getChoicesBySpecimenId()
    {
        $choices = $this->getChoices();
        $returnChoices = array();
        if (count($choices) > 0) {
            foreach ($choices as $choice) {
                if (!isset($returnChoices[$choice['specimenId']])) {
                    $returnChoices[$choice['specimenId']] = [];
                }
                unset($choice[$choice['specimenId']]);
                $returnChoices[$choice['specimenId']][] = $choice;
            }
        }
        return $returnChoices;
    }

    public function saveChoices()
    {
        $this->diffHandler->getChoices()->save($this->getChoices());
    }

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
        foreach ($datas as $key => $specimen) {
            $arraySpecimenWithEntities = $genericEntityManager->formatArraySpecimen($specimen) ;
            $datasWithChoices[$key] = $arraySpecimenWithEntities;
            foreach ($arraySpecimenWithEntities as $className => $row) {
                $key2 = null;
                if (in_array($className, $entitiesNameWithArray)) {
                    foreach ($row as $key2 => $record) {
                        foreach ($record as $fieldName => $value) {
                            $datasWithChoices[$key][$className][$key2][$fieldName] = $value;
                        }
                        $this->setChoiceForEntity($datasWithChoices, $key, $className, $record, $key2);
                    }
                } else {
                    if (!empty($row)) {
                        foreach ($row as $fieldName => $value) {
                            $datasWithChoices[$key][$className][$fieldName] = $value;
                        }
                        $this->setChoiceForEntity($datasWithChoices, $key, $className, $row);
                    }
                }
            }
        }
        return $datasWithChoices;
    }

    private function setChoiceForEntity(&$datasWithChoices, $key, $className, $arrayEntity, $key2 = null)
    {
        $choices = $this->getChoicesForEntity($className, $arrayEntity);
        if (count($choices) > 0) {
            foreach ($choices as $choice) {
                if (!is_null($key2)) {
                    $datasWithChoices[$key][$className][$key2][$choice['fieldName']] = $choice['data'];
                } else {
                    $datasWithChoices[$key][$className][$choice['fieldName']] = $choice['data'];
                }
            }
        }
    }

    public function getCsv() 
    {
        $specimenCodes = $this->sessionManager->get('specimensCode');
        $genericEntityManager = $this->genericEntityManager;
        $datas = $genericEntityManager->getEntitiesLinkedToSpecimens('recolnat', $specimenCodes);
        $datasWithChoices = $this->getArrayDatasWithChoices($datas);
        $csvExporter = new \AppBundle\Business\Exporter\CsvExporter(
                $datasWithChoices, $this->getExportDirPath(), $this->genericEntityManager);

        return $csvExporter->generate($this->user->getPrefs());
    }
    
    public function getDwc()
    {
        $specimenCodes = $this->sessionManager->get('specimensCode');
        $genericEntityManager = $this->genericEntityManager;

        $datas = $genericEntityManager->getEntitiesLinkedToSpecimens('recolnat', $specimenCodes);
        $datasWithChoices = $this->getArrayDatasWithChoices($datas);
        $dwcExporter = new \AppBundle\Business\Exporter\DwcExporter(
                $datasWithChoices, $this->getExportDirPath(), $this->genericEntityManager);

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
        } else {
            echo $className . ' ' . $this->genericEntityManager->getIdentifierName($className) . "<br/>";
            die();
        }

        if (!is_null($relationId)) {
            foreach ($this->getChoices() as $row) {
                if ($row['className'] == $className && $row['relationId'] == $relationId) {
                    $returnChoices[] = $row;
                }
            }
        }
        return $returnChoices;
    }

}
