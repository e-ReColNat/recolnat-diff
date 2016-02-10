<?php

namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

/**
 * Description of DiffStatsManager
 *
 * @author tpateffoz
 */
class DiffStatsManager
{

    private $arrayIds;

    /**
     * Holds the Doctrine entity manager for eRecolnat database interaction
     * @var EntityManager 
     */
    protected $emR;

    /**
     * Holds the Doctrine entity manager for Institution database interaction
     * @var EntityManager 
     */
    protected $emD;
    protected $diffs = array();
    protected $lonesomeRecords = array();
    protected $classes = array();
    protected $stats = array();

    public function __construct(EntityManager $emR, EntityManager $emD)
    {
        $this->emR = $emR;
        $this->emD = $emD;
        $this->diffs['datas'] = [];
        $this->diffs['classes'] = [];
    }

    /**
     * @param array $arrayIds
     * @return $this
     */
    public function init($arrayIds)
    {
        $this->arrayIds = $arrayIds;
        if (count($this->arrayIds) > 0) {
            foreach ($this->arrayIds as $class => $specimensCode) {
                $nameDiffClassManager = '\\AppBundle\\Manager\\Diff' . ucfirst(strtolower($class));
                /* @var $diffClassManager \AppBundle\Manager\DiffAbstract */
                $diffClassManager = new $nameDiffClassManager($this->emR, $this->emD);
                $diffClassManager->init($class, $specimensCode);
                $this->setDiffs($class, $diffClassManager->getStats());
                $this->setLonesomeRecords($class, $diffClassManager->getLonesomeRecords());
                $this->computeDiffs($class);
                unset($diffClassManager);
            }
        }
        $this->diffs['classes'] = $this->classes ;
        return $this;
    }

    /**
     * @param string $specimenCode
     */
    private function setTaxon($specimenCode) {
        if (!isset($this->diffs['datas'][$specimenCode]['display'])) {
            $taxonRepository = $this->emR->getRepository('\AppBundle\Entity\Taxon') ;
            $taxon = $taxonRepository->findBestTaxonsBySpecimenCode($specimenCode);
            $this->diffs['datas'][$specimenCode]['taxon'] = $taxon instanceof \AppBundle\Entity\Taxon ? $taxon->__toString() : '';
        }
    }

    /**
     * @param string $className
     */
    private function computeDiffs($className)
    {
        $this->stats[$className]=[];
        if (isset($this->diffs['classes'][$className])) {
            foreach ($this->diffs['classes'][$className] as $specimenCode => $rows) {
                $this->setTaxon($specimenCode) ;
                if (!isset($this->diffs['datas'][$specimenCode])) {
                    $this->diffs['datas'][$specimenCode] = [];
                    $this->diffs['datas'][$specimenCode]['classes'] = [];
                }
                if (!isset($this->diffs['datas'][$specimenCode]['classes'][$className])) {
                    $this->diffs['datas'][$specimenCode]['classes'][$className] = [];
                }
                foreach ($rows as $recordId => $fields) {
                    $this->setStatsForClass($className, $fields);
                    $this->diffs['datas'][$specimenCode]['classes'][$className]['fields'] = $fields;
                    $this->diffs['datas'][$specimenCode]['classes'][$className]['id'] = $recordId;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getAllStats() 
    {
        return $this->stats ;
    }

    /**
     * @param string $className
     * @param array $fields
     */
    private function setStatsForClass($className, $fields) 
    {
        $fieldsName = array_keys($fields) ;
        foreach ($fieldsName as $fieldName) {
            if (!isset($this->stats[$className][$fieldName])) {
                $this->stats[$className][$fieldName] = 0;
            }
            $this->stats[$className][$fieldName]++ ;
        }
    }

    /**
     * @return array
     */
    public function getDiffs()
    {
        return $this->diffs;
    }

    /**
     * @return array
     */
    public function getLonesomeRecords()
    {
        return $this->lonesomeRecords;
    }


    /**
     * Set les enregistrements orphelins
     */
    public function setLonesomeRecords($class, $lonesomeRecords)
    {
        $this->lonesomeRecords[$class] = $lonesomeRecords ;
    }


    public function setDiffs($class, $stats)
    {
        $this->diffs['classes'][$class] = $stats;
        $this->classes[$class] = array_keys($stats) ;
    }

    public function getAllSpecimensId()
    {
        return array_keys($this->diffs['datas']);
    }

}
