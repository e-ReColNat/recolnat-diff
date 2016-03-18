<?php

namespace AppBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Stopwatch\Stopwatch;

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
     * @param string $class
     * @param array  $specimenCodes
     * @return $this
     */
    public function init($class, $specimenCodes)
    {
        $this->class = $class;
        $this->classFullName = 'AppBundle:'.ucfirst($class);
        $arrayChunkSpecimenCodes = array_chunk($specimenCodes, $this->maxNbSpecimenPerPass);
        if (count($arrayChunkSpecimenCodes)) {
            $stopwatch = new Stopwatch();
            foreach ($arrayChunkSpecimenCodes as $chunkSpecimenCodes) {
                $stopwatch->start('recolnat');
                $this->recordsRecolnat = $this->emR->getRepository($this->classFullName)
                    ->findBySpecimenCodes($chunkSpecimenCodes, AbstractQuery::HYDRATE_ARRAY);
                $event = $stopwatch->stop('recolnat');
                dump('recolnat : '.$event->getDuration());

                $stopwatch->start('insti');
                $this->recordsInstitution = $this->emD->getRepository($this->classFullName)
                    ->findBySpecimenCodes($chunkSpecimenCodes, AbstractQuery::HYDRATE_ARRAY);
                $event = $stopwatch->stop('insti');
                dump('insti : '.$event->getDuration());

                $stopwatch->start('compare');
                $this->compare();
                $event = $stopwatch->stop('compare');
                dump('compare : '.$event->getDuration());
            }
        }
        return $this;
    }

    /**
     * @param string     $fieldName
     * @param string     $specimenCode
     * @param string     $id
     * @param null|array $dataR
     * @param null|array $dataI
     */
    private function addStat($fieldName, $specimenCode, $id, $dataR = null, $dataI = null)
    {
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
        foreach ($filteredRecords['common'] as $specimenCode => $item) {
            foreach ($item as $id) {
                $this->compareFields($id, $fieldNames, $specimenCode);
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

    /**
     * @return array
     */
    private function getFilteredRecords()
    {
        $arrayRecords = ['common' => [], 'recolnat' => [], 'institution' => []];
        foreach ($this->recordsRecolnat as $specimenCode => $item) {
            if (isset($this->recordsInstitution[$specimenCode])) {
                foreach ($item as $id => $record) {
                    if (isset($this->recordsInstitution[$specimenCode][$id])) {
                        $arrayRecords['common'][$specimenCode][] = $id;
                    } else {
                        $arrayRecords['recolnat'][$specimenCode][] = $id;
                    }
                }
            } else {
                foreach ($item as $id => $record) {
                    $arrayRecords['recolnat'][$specimenCode][] = $id;
                }
            }
        }
        foreach ($this->recordsInstitution as $specimenCode => $item) {
            if (isset($this->recordsRecolnat[$specimenCode])) {
                foreach ($item as $id => $record) {
                    if (!isset($this->recordsRecolnat[$specimenCode][$id])) {
                        $arrayRecords['institution'][$specimenCode][] = $id;
                    }
                }
            } else {
                foreach ($item as $id => $record) {
                    $arrayRecords['institution'][$specimenCode][] = $id;
                }
            }
        }
        return $arrayRecords;
    }

    /**
     * @param string $idRecord
     * @param array  $fieldNames
     * @param string $specimenCode
     */
    private function compareFields($idRecord, $fieldNames, $specimenCode)
    {
        $recordRecolnat = $this->recordsRecolnat[$specimenCode][$idRecord];
        $recordInstitution = $this->recordsInstitution[$specimenCode][$idRecord];
        foreach ($fieldNames as $fieldName) {
            if (!(in_array($fieldName, $this->excludeFieldsName))) {
                $dataR = $recordRecolnat[$fieldName];
                $dataI = $recordInstitution[$fieldName];
                if ($dataR !== $dataI) {
                    $this->addStat($fieldName, $specimenCode, $idRecord, $dataR, $dataI);
                }
            }
        }
    }

    /**
     * @param string $db
     * @param mixed  $record
     * @param string $specimenCode
     */
    private function setLonesomeRecord($db, $record, $specimenCode)
    {
        $id = null;
        if (is_array($record)) {
            $id = $record[$this->getIdField()];
        } elseif (is_object($record)) {
            $id = $record->{$this->getIdSetter()}();
        }
        if (!is_null($id)) {
            $this->lonesomeRecords[$db][] = ['specimenCode' => $specimenCode, 'id' => $id];
        }
    }

}
