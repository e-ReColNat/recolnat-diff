<?php

namespace AppBundle\Manager;

use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Manager\GenericEntityManager;
use AppBundle\Business\DiffHandler;
use Symfony\Component\HttpFoundation\Request ;
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
    private $filename=null;
    protected $diffManager;
    protected $maxItemPerPage;
    
    /** @var \AppBundle\Business\DiffHandler */
    private $diffHandler;

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
        
        if (!is_null($filename)) {
            $this->filename = $filename;
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            if (!$fs->exists($this->getDataDirPath())) {
                $fs->mkdir($this->getDataDirPath(), 0755);
            }
            if (!$fs->exists($this->getExportDirPath())) {
                $fs->mkdir($this->getExportDirPath(), 0755);
            }
            chmod($this->getExportDirPath(), 0777);
            
            $this->diffHandler = new DiffHandler($this->getDataDirPath(), $this->filename);
            
            $doReload = false;
            $path = $this->diffHandler->getChoices()->getPathname();
            if ($this->diffHandler->getDiffs()->generateDiff) {
                $results =$this->diffManager->init($institutionCode);
                $this->sessionManager->set('diffs', $results['diffs']);
                $this->sessionManager->set('specimensCode', $results['specimensCode']);
                $this->sessionManager->set('stats', $results['stats']);
                $this->diffHandler->getDiffs()->saveDiffs($results['diffs'], $results['stats'], $results['specimensCode']);
                $this->diffHandler->getDiffs()->generateDiff = false;
            }
            else {
                $results =$this->diffHandler->getDiffs()->getData();
                $this->sessionManager->set('diffs', $results['diffs']);
                $this->sessionManager->set('specimensCode', $results['specimensCode']);
                $this->sessionManager->set('stats', $results['stats']);
            }
            
            if (!($this->sessionManager->has('file') || $this->sessionManager->get('file') != $filename)) {
                $doReload=true;
            }
            if (!($this->sessionManager->has('choices') ) || empty($this->sessionManager->get('choices'))) {
                $doReload=true;
            }
            if ($doReload && $fs->exists($path)) {
                $this->sessionManager->set('choices', $this->diffHandler->getChoices()->getContent());
            }
            else {
                $this->sessionManager->set('file', $filename);
            }
        }
        return $this;
    }

    public function getDiffHandler() {
        if ($this->diffHandler instanceof DiffHandler) {
            return $this->diffHandler ;
        }
        return null;
    }
    public function getSpecimenIdsAndDiffsAndStats(Request $request, $selectedClassName=null, $specimensWithChoices=[], $choicesToRemove=[])
    {
        $session = $this->sessionManager;
        $className=[];
        if (is_string($selectedClassName) && $selectedClassName!='all') {$className=[$selectedClassName];}
        if (is_array($selectedClassName)) {$className=$selectedClassName;}

        if (!is_null($request->query->get('reset', null))) {
            $session->clear();
        }
        $stats = $this->sessionManager->get('stats') ;
        $stats = $this->diffHandler->getDiffs()->filterResults($stats, $className, $specimensWithChoices, $choicesToRemove) ;
        $session->set('stats', $stats);
        return [$session->get('specimensCode'), $session->get('diffs'), $stats];
    }
    
    public function getMaxItemPerPage(Request $request) {
        $session = $this->sessionManager;
        
        $requestMaxItem=$request->get('maxItemPerPage', null);
        if (!is_null($requestMaxItem)) {
            $session->set('maxItemPerPage', (int) $requestMaxItem);
        }
        elseif (!$session->has('maxItemPerPage')) {
            $session->set('maxItemPerPage', $this->maxItemPerPage);
        }
        return $session->get('maxItemPerPage') ;
    }
    
    public function getFiles() 
    {
        $returnDirs=[];
        $institutionDir = $this->getDataDirPath() ;
        if ($handle = opendir($institutionDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && is_dir($institutionDir.$entry)) {
                    $returnDirs[] =new \AppBundle\Business\DiffHandler($institutionDir, $entry) ;
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
    public function getDataDirPath()
    {
        return realpath($this->exportPath) . '/' . $this->institutionCode.'/' ;
    }
    /**
     * 
     * @param String $institutionCode
     * @return String
     */
    public function getExportDirPath()
    {
        return $this->getDataDirPath() . $this->filename.'/export/' ;
    }
    /**
     * 
     * @param String $institutionCode
     * @return String
     */
    public function getChoicesFileName()
    {
        if(!is_null($this->filename)) {
            return $this->diffHandler->getChoices()->getPathname();
        }
        return null ;
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
        $sessionChoices=[];
        if ($this->sessionManager->has('choices')) {
            $sessionChoices = $this->sessionManager->get('choices');
        }
        $flag = false;
        $row['data'] = $this->genericEntityManager->getData($row['choice'], $row['className'], $row['fieldName'], $row['relationId']);
        if (count($sessionChoices)>0) {
            foreach ($sessionChoices as $key=>$choice) {
                if (
                        $choice['className'] == $row['className'] &&
                        $choice['fieldName'] == $row['fieldName'] &&
                        $choice['relationId'] == $row['relationId'] 
                        ) {
                    $sessionChoices[$key] = $row ;
                    $flag = true;
                }
            }
        }
        
        if (!$flag) {
            $sessionChoices[] = $row;
        }
        $this->sessionManager->set('choices', $sessionChoices);
        $this->diffHandler->getChoices()->save($sessionChoices) ;
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
        $choices = $this->getChoices() ;
        $returnChoices = [] ;
        if (count($choices) >0 ) {
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
        $choices = $this->getChoices() ;
        $returnChoices = array() ;
        if (count($choices) >0 ) {
            foreach ($choices as $choice) {
                if (!isset($returnChoices[$choice['specimenId']])) {
                    $returnChoices[$choice['specimenId']] = [];
                }
                unset($choice[$choice['specimenId']]);
                $returnChoices[$choice['specimenId']][] =$choice ;
            }
        }
        return $returnChoices;
    }
    public function saveChoices()
    {
        $this->diffHandler->getChoices()->save($this->getChoices());
    }
    public function getDatasWithChoices($entity, $className, $occurrenceId) 
    {
        $genericEntityManager = $this->genericEntityManager ;
        $relationId=$genericEntityManager->getIdentifierValue($entity);
        
        $datas =$genericEntityManager->serialize($entity) ;
        $serializeTaxon = null;
        if ($className == 'Determination') {
            $taxon = $entity->getTaxon();
            if (is_null($taxon)) {
                $taxon = new \AppBundle\Entity\Taxon();
            }
            $serializeTaxon = $genericEntityManager->serialize($taxon) ;
            $datas = array_merge($datas, $serializeTaxon) ;
        }
        if ($className == 'Recolte') {
            $localisation = $entity->getLocalisation();
            if (is_null($localisation)) {
                $localisation = new \AppBundle\Entity\Localisation();
            }
            $serializeLocalisation = $genericEntityManager->serialize($localisation) ;
            $datas = array_merge($datas, $serializeLocalisation) ;
        }
        if ($className == 'Specimen') {
            $stratigraphy = $entity->getStratigraphy();
            if (is_null($stratigraphy)) {
                $stratigraphy = new \AppBundle\Entity\Stratigraphy();
            }
            $serializeStratigraphy = $genericEntityManager->serialize($stratigraphy) ;
            $datas = array_merge($datas, $serializeStratigraphy) ;
        }
        $this->replaceDatasWithChoices($datas, $relationId, $className);
        if (!in_array($className, ['Specimen'])) {
            $datas = ['occurrenceid'=>$occurrenceId] + $datas;
        }
        foreach ($datas as $fieldName=>$value) {
            if ($value instanceof \DateTime) {
                $datas[$fieldName] = $value->format('d-m-Y') ;
            }
        }
        return $datas;
    }
    
    private function getArrayDatasWithChoices($datas) 
    {
        $datasWithChoices=$datas;
        $entitiesNameWithArray=[
            'Determination',
            'Taxon',
            'Multimedia',
            'Bibliography',
        ];
        /*$entitiesName=[
            'Specimen',     
            'Bibliography',
            'Determination',
            'Localisation',
            'Recolte',
            'Stratigraphy',
            'Taxon',
            'Multimedia'
        ];*/
        //var_dump($datas);
        //die();
        foreach ($datas as $key=>$data) {
            foreach ($data as $className => $row) {
                $key2=null;
                if (in_array($className, $entitiesNameWithArray)) {
                    foreach ($row as $key2=>$record) {
                        foreach ($record as $fieldName=>$value) {
                            $datasWithChoices[$key][$className][$key2][$fieldName] = $this->convertField($value) ;
                        }
                        $this->setChoiceForEntity($datasWithChoices, $key, $className, $record, $key2) ;
                    }
                }
                else {
                    foreach ($row as $fieldName=>$value) {
                        $datasWithChoices[$key][$className][$fieldName] = $this->convertField($value) ;
                    }
                    $this->setChoiceForEntity($datasWithChoices, $key, $className, $row) ;
                }
            }
        }
        return $datasWithChoices;
    }
    
    private function convertField($value) {
        if ($value instanceof \DateTime) {
            return $value->format('d-m-Y') ;
        }
        return $value;
    }
    
    private function setChoiceForEntity(&$datasWithChoices, $key, $className, $arrayEntity, $key2=null)
    {
        $choices = $this->getChoicesForEntity($className, $arrayEntity) ;
        if (count($choices)>0) {
            foreach ($choices as $choice) {
                if(!is_null($key2)) {
                    $datasWithChoices[$key][$className][$key2][$choice['fieldName']] = $choice['data'];
                }
                else {
                    $datasWithChoices[$key][$className][$choice['fieldName']] = $choice['data'];
                }
            }
        }
    }
    public function getDwc() {
        $specimenCodes=$this->sessionManager->get('specimensCode');
        $genericEntityManager = $this->genericEntityManager ;
        
        $datas = $genericEntityManager->getEntitiesLinkedToSpecimens('recolnat', $specimenCodes);
        $datasWithChoices=$this->getArrayDatasWithChoices($datas) ;
        $dwcExporter = new \AppBundle\Business\Exporter\DwcExporter(
                $datasWithChoices,
                $this->getExportDirPath(), 
                $this->genericEntityManager) ;
        
        //$formatDatas = $dwcExporter->formatDatas();
        //var_dump(current($formatDatas));
        return $dwcExporter->generate() ;
    }


    /**
     * 
     * @param string $className
     * @param string $relationId
     * @param string $fieldName
     * @return array
     */
    public function getChoice($className, $relationId, $fieldName) {
        $returnChoice = null;
        foreach ($this->getChoices() as $row) {
            if ($row['className'] == $className && $row['relationId'] == $relationId && $row['fieldName'] == $fieldName) {
                $returnChoice = $row['data'] ;
            }
        }
        return $returnChoice ;
    }
    
    /**
     * 
     * @param string $className
     * @param array $arrayEntity
     * @return array
     */
    public function getChoicesForEntity($className, $arrayEntity) {
        $returnChoices = null;
        $relationId = null;
        if (array_key_exists($this->genericEntityManager->getIdentifierName($className), $arrayEntity)) {
            $relationId = $arrayEntity[$this->genericEntityManager->getIdentifierName($className)];
        }
        else {
            echo $className.' '.$this->genericEntityManager->getIdentifierName($className)."<br/>";
            var_dump($arrayEntity) ;
            die();
        }

        if (!is_null($relationId)) {
            foreach ($this->getChoices() as $row) {
                if ($row['className'] == $className && $row['relationId'] == $relationId) {
                    $returnChoices[] = $row ;
                }
            }
        }
        return $returnChoices ;
    }
}
