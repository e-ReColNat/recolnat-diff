<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;

/**
 * Description of DiffInterface
 *
 * @author tpateffoz
 */
abstract class AbstractDiff
{
    protected $class;
    protected $classFullName;
    const KEY_RECOLNAT = 0;
    const KEY_INSTITUTION = 1;
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
    public $lonesomeRecords = [self::KEY_RECOLNAT => [], self::KEY_INSTITUTION => []];
    /**
     * Holds the Doctrine entity manager for eRecolnat database interaction
     * @var EntityManager
     */
    protected $emR;
    /**
     * Holds the Doctrine entity manager for Institution database interaction
     * @var EntityManager
     */
    protected $emB;

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
    protected $logger;

    public static function getIdSetter(){
        throw new \LogicException('method getIdSetter must be implemented') ;
    }

    public static function getIdField(){
        throw new \LogicException('method getIdField must be implemented') ;
    }
    /**
     * DiffAbstract constructor.
     * @param ManagerRegistry $managerRegistry
     * @param int             $maxNbSpecimenPerPass
 * @param Logger            $logger
     */
    public function __construct(ManagerRegistry $managerRegistry, $maxNbSpecimenPerPass, Logger $logger)
    {
        $this->maxNbSpecimenPerPass = $maxNbSpecimenPerPass;
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
        $this->emR = $managerRegistry->getManager('default');
        $this->emB = $managerRegistry->getManager('buffer');
    }

    public static function getKeysRef()
    {
        return ['recolnat'=>self::KEY_RECOLNAT, 'institution'=>self::KEY_INSTITUTION];
    }
    /**
     * @param Collection $collection
     * @param string     $class
     * @param array      $catalogNumbers
     * @return $this
     */
    public function init(Collection $collection, $class, $catalogNumbers)
    {
        $this->class = $class;
        $this->classFullName = 'AppBundle:'.ucfirst($class);
        $repositoryRecolnat = $this->emR->getRepository($this->classFullName);
        $repositoryRecolnat->setLogger($this->logger);
        $repositoryInstitution = $this->emB->getRepository($this->classFullName);
        $repositoryInstitution->setLogger($this->logger);
        $arrayChunkCatalogNumbers = array_chunk($catalogNumbers, $this->maxNbSpecimenPerPass);
        $compt=0;
        $comptRecords=0;
        if (count($arrayChunkCatalogNumbers)) {
            foreach ($arrayChunkCatalogNumbers as $chunkCatalogNumbers) {
                $strDebug = sprintf('passage #%d enregistrement %d->%d/%d', ++$compt, $comptRecords, $comptRecords+count($chunkCatalogNumbers), count($catalogNumbers));
                $this->logger->debug($strDebug);
                $comptRecords=$comptRecords+count($chunkCatalogNumbers);

                $this->recordsRecolnat = $repositoryRecolnat
                    ->findByCatalogNumbersAndId($collection, $chunkCatalogNumbers, AbstractQuery::HYDRATE_ARRAY);

                $this->recordsInstitution = $repositoryInstitution
                    ->findByCatalogNumbersAndId($collection, $chunkCatalogNumbers, AbstractQuery::HYDRATE_ARRAY);

                $this->emR->clear();
                $this->emB->clear();
                $this->logger->debug('comparaison des enregistrements passage #'.$compt);
                $this->compare();
                $this->logger->debug('fin comparaison passage #'.$compt);
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
        $this->stats[$catalogNumber][$id][$fieldName][self::KEY_RECOLNAT] = $dataR;
        $this->stats[$catalogNumber][$id][$fieldName][self::KEY_INSTITUTION] = $dataI;
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
        foreach ($filteredRecords[self::KEY_RECOLNAT] as $catalogNumber => $item) {
            foreach ($item as $id) {
                $record = $this->recordsRecolnat[$catalogNumber][$id];
                $this->setLonesomeRecord(self::KEY_RECOLNAT, $record, $catalogNumber);
            }
        }
        // Traitement des enregistrements restants de l'institution
        foreach ($filteredRecords[self::KEY_INSTITUTION] as $catalogNumber => $item) {
            foreach ($item as $id) {
                $record = $this->recordsInstitution[$catalogNumber][$id];
                $this->setLonesomeRecord(self::KEY_INSTITUTION, $record, $catalogNumber);
            }
        }
    }

    /**
     * @return array
     */
    private function getFilteredRecords()
    {
        $arrayRecords = ['common' => [], self::KEY_RECOLNAT => [], self::KEY_INSTITUTION => []];
        foreach ($this->recordsRecolnat as $catalogNumber => $item) {
            if (isset($this->recordsInstitution[$catalogNumber])) {
                foreach ($item as $id => $record) {
                    if (isset($this->recordsInstitution[$catalogNumber][$id])) {
                        $arrayRecords['common'][$catalogNumber][] = $id;
                    } else {
                        $arrayRecords[self::KEY_RECOLNAT][$catalogNumber][] = $id;
                    }
                }
            } else {
                foreach ($item as $id => $record) {
                    $arrayRecords[self::KEY_RECOLNAT][$catalogNumber][] = $id;
                }
            }
        }
        foreach ($this->recordsInstitution as $catalogNumber => $item) {
            if (isset($this->recordsRecolnat[$catalogNumber])) {
                foreach ($item as $id => $record) {
                    if (!isset($this->recordsRecolnat[$catalogNumber][$id])) {
                        $arrayRecords[self::KEY_INSTITUTION][$catalogNumber][] = $id;
                    }
                }
            } else {
                foreach ($item as $id => $record) {
                    $arrayRecords[self::KEY_INSTITUTION][$catalogNumber][] = $id;
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
                $idRecord = strtoupper($recordRecolnat[$this->getIdField()]);
                $dataR = $recordRecolnat[$fieldName];
                $dataI = $recordInstitution[$fieldName];
                if ($dataR instanceof \DateTime) {
                    /** @var \DateTime $dataR */
                    $dataR = $dataR->format('c');
                }
                if ($dataI instanceof \DateTime) {
                    /** @var \DateTime $dataI */
                    $dataI = $dataI->format('c');
                }
                if ($dataR !== $dataI) {
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
            $this->lonesomeRecords[$db][] = ['catalogNumber' => $catalogNumber, 'id' => strtoupper($id)];
        }
    }

}
