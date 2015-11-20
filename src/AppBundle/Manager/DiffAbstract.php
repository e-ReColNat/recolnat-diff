<?php

namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Description of DiffInterface
 *
 * @author tpateffoz
 */
abstract class DiffAbstract
{
    protected $class ;
    protected $classFullName ;
    /**
     * Records set venant de la base Recolnat
     * @var array
     */
    public $recordsRecolnat;
    
    /**
     * Records set venant du fichier de l'institution
     * @var array
     */
    public $recordsInstitution;
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
    protected $fields=array();
    public $excludeFieldsName = [] ;
    
    abstract protected function getIdSetter();
    
    public function __construct(EntityManager $emR, EntityManager $emD)
    {
        $this->emR = $emR;
        $this->emD = $emD;
    }

    public function init($class, $ids)
    {
        $this->class = $class ;
        $this->classFullName = 'AppBundle:'.ucfirst($class) ;
        $this->recordsRecolnat = $this->emR->getRepository($this->classFullName)
                ->findBySpecimenCodes($ids) ;
        $this->recordsInstitution = $this->emD->getRepository($this->classFullName)
                ->findBySpecimenCodes($ids) ;
        $this->compare();
        return $this;
    }
    protected function addStat($fieldName, $specimenId, $id, $dataR, $dataI)
    {
         if (!isset($this->fields)) {
            $this->fields = [];
         }
         if (!isset($this->fields[$fieldName])) {
            $this->fields[$fieldName] = 0;
         }
         if (!isset($this->stats[$specimenId])) {
            $this->stats[$specimenId] = [];
         }
         if (!isset($this->stats[$specimenId][$id])) {
            $this->stats[$specimenId][$id] = [];
         }
         $this->stats[$specimenId][$id][$fieldName] = [];
         $this->stats[$specimenId][$id][$fieldName]['recolnat'] = $dataR;
         $this->stats[$specimenId][$id][$fieldName]['institution'] = $dataI;
         $this->fields[$fieldName]++;
    }
    
    public function getStats()
    {
        return $this->stats;
    }
    public function getFields()
    {
        return $this->fields;
    }
    
    protected function compare() {
        
        $metadata = $this->emR->getMetadataFactory()->getMetadataFor($this->classFullName) ;
         
        $fieldNames = $metadata->getFieldNames();
        foreach ($this->recordsRecolnat as $specimenId=>$diffRecordsRecolnat) {
            if (isset($this->recordsInstitution[$specimenId])) {
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
}
