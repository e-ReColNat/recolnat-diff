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
    private $arrayIds ;
    
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

    protected $stats=array();
    
    public function __construct(EntityManager $emR, EntityManager $emD)
    {
        $this->emR = $emR;
        $this->emD = $emD;
        $this->stats['summary'] = [];
    }
    
    public function init($arrayIds) {
        $this->arrayIds = $arrayIds;
        //$this->arrayIds = array('recoltes'=>$arrayIds['recoltes']);
        if (count($this->arrayIds) > 0) {
            foreach ($this->arrayIds as $class => $ids) {
                $nameDiffClassManager = '\\AppBundle\\Manager\\Diff'.ucfirst(strtolower($class)) ;
                /* @var $diffClassManager \AppBundle\Manager\DiffAbstract */
                $diffClassManager = new $nameDiffClassManager($this->emR, $this->emD) ;
                $diffClassManager->init($ids) ;
                $this->addStats($class, $diffClassManager->getStats());
                $this->computeStats($class);
            }
        }
        return $this;
    }
    
    private function computeStats($class) {
        if (isset($this->stats['classes'][$class]['fields'])) {
            foreach($this->stats['classes'][$class]['fields'] as $fieldName => $row) {
                foreach ($row['specimenIds'] as $id) {
                    if (!isset($this->stats['summary'][$id])) {
                        $this->stats['summary'][$id] = [];
                    }
                    if (!isset($this->stats['summary'][$id][$class])) {
                        $this->stats['summary'][$id][$class] = 0 ;
                    }
                    $this->stats['summary'][$id][$class] ++  ;
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
        if (!isset($this->stats['classes'])) {
            $this->stats['classes'] = [];
        }
        $this->stats['classes'][$class] = $stats;
    }
    
    public function getAllSpecimensId()  {
        return array_keys($this->stats['summary']) ;
    }
}
