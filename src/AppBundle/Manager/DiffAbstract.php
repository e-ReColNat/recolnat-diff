<?php

namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
/**
 * Description of DiffInterface
 *
 * @author tpateffoz
 */
abstract class DiffAbstract
{
    abstract public function __construct(EntityManager $emR, EntityManager $emD);
    abstract public function init($ids);
    abstract protected function getIdSetter();
    
    protected function addStat($fieldName, $occurrenceId)
    {
        if (!isset($this->stats['records'])) {
            $this->stats['records'] = array() ;
        }
        if (!isset($this->stats['records'][$occurrenceId])) {
            $this->stats['records'][$occurrenceId] = array() ;
        }
        
        if (!isset($this->stats['fields'])) {
            $this->stats['fields'] = array() ;
        }
        
        if (!isset($this->stats['fields'][$fieldName])) {
            $this->stats['fields'][$fieldName] = [] ;
            $this->stats['fields'][$fieldName]['compt']=0 ; 
            $this->stats['fields'][$fieldName]['compt']=0 ; 
            $this->stats['fields'][$fieldName]['id'] = [];
        }
         $this->stats['fields'][$fieldName]['compt']++ ; 
         $this->stats['fields'][$fieldName]['id'][] = $occurrenceId;
         $this->stats['records'][$occurrenceId][] = $fieldName;
    }
    
    public function getStats()
    {
        return $this->stats;
    }
    
    protected function compare($class) {
        
        $metadata = $this->emR->getMetadataFactory()->getMetadataFor('AppBundle:'.$class) ;
         
        $fieldNames = $metadata->getFieldNames();
        foreach ($this->recordsRecolnat as $binOccurrenceId=>$recordRecolnat) {
            /* @var $recordRecolnat \AppBundle\Entity\Specimen */
            /* @var $recordInstitution \AppBundle\Entity\Specimen */
            $recordInstitution = $this->recordsInstitution[$binOccurrenceId] ;
            foreach ($fieldNames as $fieldName) {
                $getter = 'get'.$fieldName ;
                if (!(in_array($fieldName, $this->excludeFieldsName)) && 
                        $recordRecolnat->{$getter}() !== $recordInstitution->{$getter}()) {
                    $this->addStat($fieldName, $recordRecolnat->{$this->getIdSetter()}());
                }
            }
        }
    }
}
