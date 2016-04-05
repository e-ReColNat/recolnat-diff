<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Collection;
use AppBundle\Entity\Specimen;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;

/**
 * Description of DiffInterface
 *
 * @author tpateffoz
 */
abstract class AbstractDiff
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

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    protected $stats = array();
    protected $fields = array();
    public $excludeFieldsName = [];

    /**
     * Nombre max de specimens à requêter par pass
     */
    public $maxNbSpecimenPerPass;

    abstract protected function getIdSetter();

    abstract protected function getIdField();

    /**
     * DiffAbstract constructor.
     * @param ManagerRegistry $managerRegistry
     * @param int             $maxNbSpecimenPerPass
     */
    public function __construct(ManagerRegistry $managerRegistry, $maxNbSpecimenPerPass)
    {
        $this->maxNbSpecimenPerPass = $maxNbSpecimenPerPass;
        $this->managerRegistry = $managerRegistry;
        $this->emR = $managerRegistry->getManager('default');
        $this->emD = $managerRegistry->getManager('diff');
    }

    /**
     * @param Collection $collection
     * @param string     $class
     * @param array      $catalogNumber
     * @return $this
     */
    public function init(Collection $collection, $class, $catalogNumber)
    {
        $this->class = $class;
        $this->classFullName = 'AppBundle:'.ucfirst($class);
        $arrayChunkCatalogNumbers = array_chunk($catalogNumber, $this->maxNbSpecimenPerPass);
        if (count($arrayChunkCatalogNumbers)) {
            foreach ($arrayChunkCatalogNumbers as $chunkCatalogNumbers) {
                $this->recordsRecolnat = $this->emR->getRepository($this->classFullName)
                    ->findByCatalogNumbers($collection, $chunkCatalogNumbers, AbstractQuery::HYDRATE_ARRAY);

                $this->recordsInstitution = $this->emD->getRepository($this->classFullName)
                    ->findByCatalogNumbers($collection, $chunkCatalogNumbers, AbstractQuery::HYDRATE_ARRAY);

                $this->compare();
            }
        }

        return $this;
    }

    /**
     * @param string     $fieldName
     * @param string     $catalogNumber
     * @param string     $id
     * @param null|mixed $dataR
     * @param null|mixed $dataI
     */
    private function addStat($fieldName, $catalogNumber, $id, $dataR = null, $dataI = null)
    {
        if (!isset($this->fields[$fieldName])) {
            $this->fields[$fieldName] = 0;
        }
        if (!isset($this->stats[$catalogNumber])) {
            $this->stats[$catalogNumber] = [];
            $this->stats[$catalogNumber][$id] = [];
        }
        $this->stats[$catalogNumber][$id][$fieldName] = [];
        $this->stats[$catalogNumber][$id][$fieldName]['recolnat'] = $dataR;
        $this->stats[$catalogNumber][$id][$fieldName]['institution'] = $dataI;
        $this->fields[$fieldName]++;
    }

    /**
     * @return array
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @return array
     */
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
        foreach ($filteredRecords['common'] as $catalogNumber => $item) {
            foreach ($item as $id) {
                $this->compareFields($id, $fieldNames, $catalogNumber);
            }
        }

        // Traitement des enregistrements restants de recolnat
        foreach ($filteredRecords['recolnat'] as $catalogNumber => $item) {
            foreach ($item as $id) {
                $record = $this->recordsRecolnat[$catalogNumber][$id];
                $this->setLonesomeRecord('recolnat', $record, $catalogNumber);
            }
        }
        // Traitement des enregistrements restants de l'institution
        foreach ($filteredRecords['institution'] as $catalogNumber => $item) {
            foreach ($item as $id) {
                $record = $this->recordsInstitution[$catalogNumber][$id];
                $this->setLonesomeRecord('institution', $record, $catalogNumber);
            }
        }
    }

    /**
     * @return array
     */
    private function getFilteredRecords()
    {
        $arrayRecords = ['common' => [], 'recolnat' => [], 'institution' => []];
        foreach ($this->recordsRecolnat as $catalogNumber => $item) {
            if (isset($this->recordsInstitution[$catalogNumber])) {
                foreach ($item as $id => $record) {
                    if (isset($this->recordsInstitution[$catalogNumber][$id])) {
                        $arrayRecords['common'][$catalogNumber][] = $id;
                    } else {
                        $arrayRecords['recolnat'][$catalogNumber][] = $id;
                    }
                }
            } else {
                foreach ($item as $id => $record) {
                    $arrayRecords['recolnat'][$catalogNumber][] = $id;
                }
            }
        }
        foreach ($this->recordsInstitution as $catalogNumber => $item) {
            if (isset($this->recordsRecolnat[$catalogNumber])) {
                foreach ($item as $id => $record) {
                    if (!isset($this->recordsRecolnat[$catalogNumber][$id])) {
                        $arrayRecords['institution'][$catalogNumber][] = $id;
                    }
                }
            } else {
                foreach ($item as $id => $record) {
                    $arrayRecords['institution'][$catalogNumber][] = $id;
                }
            }
        }

        return $arrayRecords;
    }

    /**
     * @param string $idRecord
     * @param array  $fieldNames
     * @param string $catalogNumber
     */
    private function compareFields($idRecord, $fieldNames, $catalogNumber)
    {
        $recordRecolnat = $this->recordsRecolnat[$catalogNumber][$idRecord];
        $recordInstitution = $this->recordsInstitution[$catalogNumber][$idRecord];
        foreach ($fieldNames as $fieldName) {
            if (!(in_array($fieldName, $this->excludeFieldsName))) {
                $dataR = $recordRecolnat[$fieldName];
                $dataI = $recordInstitution[$fieldName];
                if ($dataR instanceof \DateTime && $dataI instanceof \DateTime) {
                    /** @var \DateTime $dataR */
                    /** @var \DateTime $dataI */
                    if ($dataR->format('c') !== $dataI->format('c')) {
                        $this->addStat($fieldName, $catalogNumber, $idRecord, $dataR, $dataI);
                    }
                } elseif ($dataR !== $dataI) {
                    $this->addStat($fieldName, $catalogNumber, $idRecord, $dataR, $dataI);
                }
            }
        }
    }

    /**
     * @param string $db
     * @param mixed  $record
     * @param string $catalogNumber
     */
    private function setLonesomeRecord($db, $record, $catalogNumber)
    {
        $id = null;
        if (is_array($record)) {
            $id = $record[$this->getIdField()];
        } elseif (is_object($record)) {
            $id = $record->{$this->getIdSetter()}();
        }
        if (!is_null($id)) {
            $this->lonesomeRecords[$db][] = ['code' => $catalogNumber, 'id' => $id];
        }
    }

}
