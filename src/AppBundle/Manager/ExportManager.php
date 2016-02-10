<?php

namespace AppBundle\Manager;

use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Manager\GenericEntityManager;
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
    private $sessionManager;
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
     * 
     * @param String $institutionCode
     * @return \AppBundle\Manager\ExportManager
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
    public function getStatsLonesomeRecords() {
        $lonesomeRecords = $this->getLoneSomeRecords() ;
        dump($lonesomeRecords);
        $stats = [];
        $refRecolnatSpecimenCode = array_column($lonesomeRecords['Specimen']['recolnat'], 'specimenCode') ;
        $refInstitutionSpecimenCode = array_column($lonesomeRecords['Specimen']['institution'], 'specimenCode') ;
        foreach ($lonesomeRecords as $className => $items) {
            // si la className n'est pas specimen et que l'enregistrement est déjà présent dans les
            // spécimens alors on a affaire à un nouveau specimen donc on l'enlève du décompte
            if ($className !== 'Specimen') {
                $specimenCodes = array_column($items['recolnat'], 'specimenCode');
                $stats[$className]['recolnat'] = count(array_diff($specimenCodes, $refRecolnatSpecimenCode));
                $specimenCodes = array_column($items['institution'], 'specimenCode');
                $stats[$className]['institution'] = count(array_diff($specimenCodes, $refInstitutionSpecimenCode));
            }
            else {
                $stats[$className]['recolnat'] = count($items['recolnat']);
                $stats[$className]['institution'] = count($items['institution']);
            }
        }
        return $stats;
    }

    public function getSumLonesomeRecords() {
        $statsLonesomeRecords=$this->getStatsLonesomeRecords();
        $sumLonesomeRecords=['recolnat'=>0, 'institution'=>0];
        foreach ($statsLonesomeRecords as $lonesomeRecords) {
            $sumLonesomeRecords['recolnat']+=$lonesomeRecords['recolnat'];
            $sumLonesomeRecords['institution']+=$lonesomeRecords['institution'];
        }
        return $sumLonesomeRecords;
    }
    public function getStats()
    {
        return $this->sessionManager->get('stats');
    }

    public function getSumStats()
    {
        $stats = $this->getExpandedStats();
        $sumStats = ['specimens' => 0, 'diffs' => 0, 'fields' => 0];
        foreach ($stats as $datas) {
            $sumStats['specimens']+=$datas['specimens'];
            $sumStats['diffs']+=$datas['diffs'];
            $sumStats['fields']+=count($datas['fields']);
        }
        return $sumStats;
    }

    /**
     * Renvoie les stats des diffs avec les données
     * @param string $order
     * @return array
     */
    public function getExpandedStats($order = 'desc')
    {
        $stats = [];
        $diffs = $this->sessionManager->get('diffs');
        foreach ($this->getStats() as $className => $fields) {
            $stats[$className]['diffs'] = array_sum($fields);
            $tempFields = $fields;
            switch ($order) {
                case 'desc':
                    arsort($tempFields);
                    break;
                case 'asc':
                    asort($tempFields);
                    break;
            }
            $stats[$className]['fields'] = $tempFields;
            $stats[$className]['specimens'] = count($diffs['classes'][$className]);
        }
        return $stats;
    }

    /**
     * Renvoie les statistiques de diffs présentant les mêmes données modifiées pour des champs identiques
     * @param array $classesName
     * @param string $dateFormat
     * @return array
     */
    public function getStatsBySimilarity($classesName = [], $dateFormat ='d/M/Y')
    {
        $diffs = $this->sessionManager->get('diffs');
        if (empty($classesName)) {
            $classesName = array_keys($diffs['classes']) ;
        }
        array_map(function($value) {
            return ucfirst(strtolower($value)) ;
        }, $classesName) ;
        
        $dataSeparator = '\#|#/';
        $stats = [];
        foreach ($classesName as $className) {
            if (isset($diffs['classes'][$className]) && !empty($diffs['classes'][$className])) {
                foreach ($diffs['classes'][$className] as $specimenCode) {
                    if (isset($diffs['datas'][$specimenCode])) {
                        $details = $diffs['datas'][$specimenCode]['classes'][$className] ;
                        $taxon = $diffs['datas'][$specimenCode]['taxon'] ;
                        foreach ($details['fields'] as $fieldName => $datas) {
                            // Traitement des dates
                            if (is_array($datas['recolnat']) && isset($datas['recolnat']['date'])) {
                                $date = new \DateTime($datas['recolnat']['date']) ;
                                $datas['recolnat'] = $date->format($dateFormat)  ;
                            }
                            if (is_array($datas['institution']) && isset($datas['institution']['date'])) {
                                $date = new \DateTime($datas['institution']['date']) ;
                                $datas['institution'] = $date->format($dateFormat)  ;
                            }
                            // Création d'une clé unique
                            $concatDatas = md5(implode($dataSeparator, [$className, $fieldName, $datas['recolnat'], $datas['institution']])) ;

                            if (!isset($stats[$concatDatas])) {
                                $stats[$concatDatas] = ['taxons'=>[], 'specimensCode'=>[]];
                            }

                            $stats[$concatDatas]['specimensCode'][$specimenCode] = $details['id'];
                            $stats[$concatDatas]['taxons'][$specimenCode] = $taxon;
                            $stats[$concatDatas]['datas'] = $datas;
                            $stats[$concatDatas]['className'] = $className;
                            $stats[$concatDatas]['fieldName'] = $fieldName;
                        }
                    }
                }
            }
        }
        uasort($stats, function ($a, $b) {
                $a = count($a['specimensCode']);
                $b = count($b['specimensCode']);
                return ($a == $b) ? 0 : (($a > $b) ? -1 : 1);
            });
        return $stats;
    }
    
    public function getCondensedStats()
    {
        $stats = [];
        foreach ($this->getStats() as $className => $fields) {
            $stats[$className] = array_sum($fields);
        }
        return $stats;
    }

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

    public function getDiffsBySpecimensCode($specimensCode) 
    {
        $allDiffs = $this->sessionManager->get('diffs');
        $diffs = $this->diffHandler->getDiffs()->filterBySpecimensCode($allDiffs, $specimensCode);
        return $diffs;
    }
    
    public function getSpecimensCode()
    {
        $stats = $this->sessionManager->get('diffs');
        return array_keys($stats['datas']);
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

    public function getStatsChoices()
    {
        $choices = $this->getChoicesForDisplay();

        $statsChoices = [];
        $callbackCountChoices = function ($value, $className) use (&$statsChoices) {
            if (is_array($value)) {
                if (!isset($statsChoices[$className])) {
                    $statsChoices[$className] = 0;
                }
                foreach ($value as $row) {
                    foreach ($row as $fields) {
                        $statsChoices[$className] += count($fields);
                    }
                }
            }
        };

        array_walk($choices, $callbackCountChoices);
        $statsChoices['sum'] = array_sum($statsChoices);
        return $statsChoices;
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
            $arraySpecimenWithEntities = $genericEntityManager->formatArraySpecimen($specimen);
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
        if (is_array($choices) && count($choices) > 0) {
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
        $datas = $this->genericEntityManager->getEntitiesLinkedToSpecimens('recolnat', $specimenCodes);
        $datasWithChoices = $this->getArrayDatasWithChoices($datas);
        $csvExporter = new CsvExporter(
                $datasWithChoices, $this->getExportDirPath());

        return $csvExporter->generate($this->user->getPrefs());
    }

    public function getDwc()
    {
        $specimenCodes = $this->sessionManager->get('specimensCode');
        $datas = $this->genericEntityManager->getEntitiesLinkedToSpecimens('recolnat', $specimenCodes);
        $datasWithChoices = $this->getArrayDatasWithChoices($datas);
        $dwcExporter = new DwcExporter(
                $datasWithChoices, $this->getExportDirPath());

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
