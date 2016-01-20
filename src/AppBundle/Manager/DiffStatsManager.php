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
    protected $classes = array();
    protected $stats = array();

    public function __construct(EntityManager $emR, EntityManager $emD)
    {
        $this->emR = $emR;
        $this->emD = $emD;
        $this->diffs['summary'] = [];
        $this->diffs['classes'] = [];
    }

    public function init($arrayIds)
    {
        $this->arrayIds = $arrayIds;
        $taxonRepository = $this->emR->getRepository('\AppBundle\entity\Specimen') ;
        if (count($this->arrayIds) > 0) {
            foreach ($this->arrayIds as $class => $specimensCode) {
                $nameDiffClassManager = '\\AppBundle\\Manager\\Diff' . ucfirst(strtolower($class));
                /* @var $diffClassManager \AppBundle\Manager\DiffAbstract */
                $diffClassManager = new $nameDiffClassManager($this->emR, $this->emD);
                $diffClassManager->init($class, $specimensCode);
                $this->addDiffs($class, $diffClassManager->getStats());
                $this->computeDiffs($class);
            }
        }
        $this->diffs['classes'] = $this->classes ;
        return $this;
    }
    private function setTaxon($specimenCode) {
        if (!isset($this->diffs['summary'][$specimenCode]['display'])) {
            $taxonRepository = $this->emR->getRepository('\AppBundle\Entity\Taxon') ;
            $taxon = $taxonRepository->findBestTaxonsBySpecimenCode($specimenCode);
            $this->diffs['summary'][$specimenCode]['taxon'] = $taxon instanceof \AppBundle\Entity\Taxon ? $taxon->__toString() : '';
        }
    }
    private function computeDiffs($className)
    {
        $this->stats[$className]=[];
        if (isset($this->diffs['classes'][$className])) {
            foreach ($this->diffs['classes'][$className] as $specimenCode => $rows) {
                $this->setTaxon($specimenCode) ;
                if (!isset($this->diffs['summary'][$specimenCode])) {
                    $this->diffs['summary'][$specimenCode] = [];
                    $this->diffs['summary'][$specimenCode]['classes'] = [];
                }
                if (!isset($this->diffs['summary'][$specimenCode]['classes'][$className])) {
                    $this->diffs['summary'][$specimenCode]['classes'][$className] = [];
                }
                foreach ($rows as $recordId => $fields) {
                    $this->setStatsForClass($className, $fields);
                    $this->diffs['summary'][$specimenCode]['classes'][$className]['fields'] = $fields;
                    $this->diffs['summary'][$specimenCode]['classes'][$className]['id'] = $recordId;
                }
            }
        }
    }

    public function getAllStats() 
    {
        return $this->stats ;
    }
    
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
    public function getDiffs()
    {
        return $this->diffs;
    }

    public function addDiffs($class, $stats)
    {
        $this->diffs['classes'][$class] = $stats;
        $this->classes[$class] = array_keys($stats) ;
    }

    public function getAllSpecimensId()
    {
        return array_keys($this->diffs['summary']);
    }

}
