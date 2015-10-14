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
    }
    
    public function init($arrayIds) {
        $this->arrayIds = $arrayIds;
        if (count($this->arrayIds) > 0) {
            foreach ($this->arrayIds as $class => $ids) {
                $nameDiffClassManager = '\\AppBundle\\Manager\\Diff'.ucfirst(strtolower($class)) ;
                /* @var $diffClassManager \AppBundle\Manager\DiffAbstract */
                $diffClassManager = new $nameDiffClassManager($this->emR, $this->emD) ;
                $diffClassManager->init($ids) ;
                $this->addStats($class, $diffClassManager->getStats());
            }
        }
        return $this;
    }
    
    public function getStats()
    {
        return $this->stats;
    }

    public function addStats($class, $stats)
    {
        $this->stats[$class] = $stats;
    }
}
