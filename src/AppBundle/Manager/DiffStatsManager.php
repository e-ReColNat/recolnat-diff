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
    protected $stats = array();

    public function __construct(EntityManager $emR, EntityManager $emD)
    {
        $this->emR = $emR;
        $this->emD = $emD;
        $this->stats['summary'] = [];
        $this->stats['classes'] = [];
        $this->stats['taxons'] = [];
    }

    public function init($arrayIds)
    {
        $this->arrayIds = $arrayIds;
        $taxonRepository = $this->emR->getRepository('\AppBundle\entity\Specimen') ;
        if (count($this->arrayIds) > 0) {
            foreach ($this->arrayIds as $class => $ids) {
                $nameDiffClassManager = '\\AppBundle\\Manager\\Diff' . ucfirst(strtolower($class));
                /* @var $diffClassManager \AppBundle\Manager\DiffAbstract */
                $diffClassManager = new $nameDiffClassManager($this->emR, $this->emD);
                $diffClassManager->init($class, $ids);
                $this->addStats($class, $diffClassManager->getStats());
                $this->computeStats($class);
            }
        }
        return $this;
    }
    private function computeStats($class)
    {
        $taxonRepository = $this->emR->getRepository('\AppBundle\Entity\Taxon') ;
        if (isset($this->stats['classes'][$class])) {
            foreach ($this->stats['classes'][$class] as $specimenCode => $rows) {
                if (!isset($this->stats['summary'][$specimenCode])) {
                    $this->stats['summary'][$specimenCode] = [];
                }
                if (!isset($this->stats['summary'][$specimenCode][$class])) {
                    $this->stats['summary'][$specimenCode][$class]['records'] = count($rows);
                }
                if (!isset($this->stats['summary'][$specimenCode][$class]['fields'])) {
                    $this->stats['summary'][$specimenCode][$class]['fields'] = 0;
                }
                foreach ($rows as $recordId => $fields) {
                    $this->stats['summary'][$specimenCode][$class]['fields']+=count($fields);
                }
                if (!isset($this->stats['taxons'][$specimenCode])) {
                    $taxon = $taxonRepository->findBestTaxonsBySpecimenCode($specimenCode);
                    $this->stats['taxons'][$specimenCode] = $taxon instanceof \AppBundle\Entity\Taxon ? $taxon->__toString() : '';
                }
            }
        }
    }

    public function getStats()
    {
        return $this->stats;
    }

    public function addStats($class, $stats)
    {
        $this->stats['classes'][$class] = $stats;
    }

    public function getAllSpecimensId()
    {
        return array_keys($this->stats['summary']);
    }

}
