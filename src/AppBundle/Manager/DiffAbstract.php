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
    protected $class;
    protected $classFullName;
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
     * Records set venant du fichier de l'institution
     * @var array
     */
    public $lonesomeRecords = ['recolnat' => [], 'institution' => []];
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
    protected $fields = array();
    public $excludeFieldsName = [];

    abstract protected function getIdSetter();

    public function __construct(EntityManager $emR, EntityManager $emD)
    {
        $this->emR = $emR;
        $this->emD = $emD;
    }

    public function init($class, $ids)
    {
        $this->class = $class;
        $this->classFullName = 'AppBundle:' . ucfirst($class);
        $this->recordsRecolnat = $this->emR->getRepository($this->classFullName)
            ->findBySpecimenCodes($ids);
        $this->recordsInstitution = $this->emD->getRepository($this->classFullName)
            ->findBySpecimenCodes($ids);
        $this->compare();
        return $this;
    }

    protected function addStat($fieldName, $specimenCode, $id, $dataR = null, $dataI = null)
    {
        if (!isset($this->fields)) {
            $this->fields = [];
        }
        if (!isset($this->fields[$fieldName])) {
            $this->fields[$fieldName] = 0;
        }
        if (!isset($this->stats[$specimenCode])) {
            $this->stats[$specimenCode] = [];
        }
        if (!isset($this->stats[$specimenCode][$id])) {
            $this->stats[$specimenCode][$id] = [];
        }
        $this->stats[$specimenCode][$id][$fieldName] = [];
        $this->stats[$specimenCode][$id][$fieldName]['recolnat'] = $dataR;
        $this->stats[$specimenCode][$id][$fieldName]['institution'] = $dataI;
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

    /**
     * @return array
     */
    public function getLonesomeRecords()
    {
        return $this->lonesomeRecords;
    }


    /**
     * Compare les champs un par un pour trouver les différences
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    protected function compare()
    {
        $metadata = $this->emR->getMetadataFactory()->getMetadataFor($this->classFullName);

        $fieldNames = $metadata->getFieldNames();
        $filteredRecords = $this->getFilteredRecords();
        // Traitement des enregistrements communs aux deux bases
        foreach ($filteredRecords['common'] as $specimenCode => $item) {
            foreach($item as $id) {
                $this->compareFields( $id, $fieldNames, $specimenCode);
            }
        }

        // Traitement des enregistrements restants de recolnat
        foreach ($filteredRecords['recolnat'] as $specimenCode => $item) {
            foreach ($item as $id) {
                $record = $this->recordsRecolnat[$specimenCode][$id];
                $this->setLonesomeRecord('recolnat', $record, $specimenCode);
            }
        }
        // Traitement des enregistrements restants de l'institution
        foreach ($filteredRecords['institution'] as $specimenCode => $item) {
            foreach ($item as $id) {
                $record = $this->recordsInstitution[$specimenCode][$id];
                $this->setLonesomeRecord('institution', $record, $specimenCode);
            }
        }
    }

    private function getFilteredRecords()
    {
        $arrayRecords=['common'=>[], 'recolnat'=>[], 'institution'=>[]];
        foreach ($this->recordsRecolnat as $specimenCode => $item) {
            if (isset($this->recordsInstitution[$specimenCode])) {
                foreach($item as $id => $record) {
                    if (isset($this->recordsInstitution[$specimenCode][$id])) {
                        $arrayRecords['common'][$specimenCode][]=$id;
                    }
                    else {
                        $arrayRecords['recolnat'][$specimenCode][]=$id;
                    }
                }
            }
            else {
                foreach($item as $id => $record) {
                    $arrayRecords['recolnat'][$specimenCode][]=$id;
                }
            }
        }
        foreach ($this->recordsInstitution as $specimenCode => $item) {
            if (isset($this->recordsRecolnat[$specimenCode])) {
                foreach($item as $id => $record) {
                    if (!isset($this->recordsRecolnat[$specimenCode][$id])) {
                        $arrayRecords['institution'][$specimenCode][]=$id;
                    }
                }
            }
            else {
                foreach($item as $id => $record) {
                    $arrayRecords['institution'][$specimenCode][]=$id;
                }
            }
        }
        return $arrayRecords;
    }
    /**
     * @param $diffRecordsdb2
     * @param $idRecord
     * @param $fieldNames
     * @param $recordDb1
     * @param $specimenCode
     */
    private function compareFields($idRecord, $fieldNames, $specimenCode)
    {
        $recordRecolnat = $this->recordsRecolnat[$specimenCode][$idRecord];
        $recordInstitution = $this->recordsInstitution[$specimenCode][$idRecord];
        foreach ($fieldNames as $fieldName) {
            if (!(in_array($fieldName, $this->excludeFieldsName))) {
                $getter = 'get' . $fieldName;
                $dataR = $recordRecolnat->{$getter}();
                $dataI = $recordInstitution->{$getter}();
                if ($dataR !== $dataI) {
                    $this->addStat($fieldName, $specimenCode, $idRecord, $dataR, $dataI);
                }
            }
        }
    }

    /**
     * @param $db
     * @param $record
     * @param $specimenCode
     */
    private function setLonesomeRecord($db, $record, $specimenCode)
    {
        $id=null;
        if (is_array($record)) {
            $id = key($record);
        } elseif (is_object($record)) {
            $id = $record->{$this->getIdSetter()}();
        }
        if(!is_null($id)) {
            $this->lonesomeRecords[$db][] = ['specimenCode' => $specimenCode, 'id' => $id];
        }
    }
}
