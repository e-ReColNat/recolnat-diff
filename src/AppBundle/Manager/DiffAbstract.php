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
    
    protected function addStat($fieldName, $specimenId, $id, $dataR, $dataI)
    {
        if (!isset($this->stats['records'])) {
            $this->stats['records'] = array() ;
        }
        if (!isset($this->stats['records'][$id])) {
            $this->stats['records'][$id] = array() ;
        }
        
        if (!isset($this->stats['fields'])) {
            $this->stats['fields'] = array() ;
        }
        
        if (!isset($this->stats['fields'][$fieldName])) {
            $this->stats['fields'][$fieldName] = [] ;
            $this->stats['fields'][$fieldName]['compt']=0 ; 
            $this->stats['fields'][$fieldName]['compt']=0 ; 
            $this->stats['fields'][$fieldName]['ids'] = [];
        }
         $this->stats['fields'][$fieldName]['compt']++ ; 
         $this->stats['fields'][$fieldName]['ids'][] = $id;
         $this->stats['fields'][$fieldName]['specimenIds'][] = $specimenId;

         $this->stats['records'][$id][$fieldName] = [];
         $this->stats['records'][$id][$fieldName]['recolnat'] = $dataR;
         $this->stats['records'][$id][$fieldName]['institution'] = $dataI;
    }
    
    public function getStats()
    {
        return $this->stats;
    }
    
    protected function compare($class) {
        
        $metadata = $this->emR->getMetadataFactory()->getMetadataFor('AppBundle:'.$class) ;
         
        $fieldNames = $metadata->getFieldNames();
        foreach ($this->recordsRecolnat as $specimenId=>$diffRecordsRecolnat) {
            $diffRecordsInstitution = $this->recordsInstitution[$specimenId] ;
            /* @var $recordRecolnat \AppBundle\Entity\Specimen */
            foreach ($diffRecordsRecolnat as $idRecord => $recordRecolnat) {
                $recordInstitution = $diffRecordsInstitution[$idRecord] ;
                foreach ($fieldNames as $fieldName) {
                    if (!(in_array($fieldName, $this->excludeFieldsName)))  {
                        $getter = 'get'.$fieldName ;
                        $dataR = $recordRecolnat->{$getter}() ;
                        $dataI = $recordInstitution->{$getter}() ;
                        if ($dataR !== $dataI) {
                            $this->addStat($fieldName,$specimenId, $idRecord, $dataR, $dataI);
                        }
                    }
                }
            }
        }
    }
}
