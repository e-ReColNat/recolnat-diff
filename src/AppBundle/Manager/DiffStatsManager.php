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
    }

    public function init($arrayIds)
    {
        $this->arrayIds = $arrayIds;
        //$this->arrayIds = array('recoltes'=>$arrayIds['recoltes']);
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
        if (isset($this->stats['classes'][$class])) {
            foreach ($this->stats['classes'][$class] as $specimenId => $rows) {
                if (!isset($this->stats['summary'][$specimenId])) {
                    $this->stats['summary'][$specimenId] = [];
                }
                if (!isset($this->stats['summary'][$specimenId][$class])) {
                    $this->stats['summary'][$specimenId][$class]['records'] = count($rows);
                }
                if (!isset($this->stats['summary'][$specimenId][$class]['fields'])) {
                    $this->stats['summary'][$specimenId][$class]['fields'] = 0;
                }
                foreach ($rows as $recordId => $fields) {
                    $this->stats['summary'][$specimenId][$class]['fields']+=count($fields);
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
