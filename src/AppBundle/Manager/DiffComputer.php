<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Taxon;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

/**
 * Description of DiffStatsManager
 *
 * @author tpateffoz
 */
class DiffComputer
{

    private $specimenCodes;

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


    protected $diffs = [];
    protected $lonesomeRecords = [];
    protected $classes = [];
    protected $stats = [];
    protected $statsLonesomeRecords = [];
    protected $taxons = [];

    private $classOrder = [
        'Specimen',
        'Bibliography',
        'Determination',
        'Multimedia',
        'Localisation',
        'Recolte',
        'Stratigraphy',
        'Taxon'
    ];

    protected $maxNbSpecimenPerPass;

    /**
     * DiffComputer constructor.
     * @param ManagerRegistry $managerRegistry
     * @param int             $maxNbSpecimenPerPass
     */
    public function __construct(ManagerRegistry $managerRegistry, $maxNbSpecimenPerPass)
    {
        $this->maxNbSpecimenPerPass = $maxNbSpecimenPerPass;
        $this->managerRegistry = $managerRegistry;
        $this->emR = $managerRegistry->getManager('default');
        $this->emD = $managerRegistry->getManager('diff');
        $this->diffs['datas'] = [];
        $this->diffs['classes'] = [];
    }

    /**
     * @param array $specimenCodes
     * @return $this
     */
    public function init($specimenCodes)
    {
        $this->setSpecimenCodes($specimenCodes);
        if (count($this->specimenCodes) > 0) {
            foreach ($this->classOrder as $className) {
                $this->computeClassname($className);
            }
        }
        $this->diffs['classes'] = $this->classes;
        return $this;
    }

    /**
     * @param $specimenCodes
     */
    public function setSpecimenCodes($specimenCodes)
    {
        $this->specimenCodes = $specimenCodes;
        $this->setTaxons();
    }

    /**
     * @param $className
     */
    public function computeClassname($className)
    {
        if (isset($this->specimenCodes[ucfirst($className)])) {
            $specimensCode = $this->specimenCodes[$className];
            $nameDiffClassManager = '\\AppBundle\\Manager\\Diff'.ucfirst(strtolower($className));
            /* @var $diffClassManager \AppBundle\Manager\AbstractDiff */
            $diffClassManager = new $nameDiffClassManager($this->managerRegistry, $this->maxNbSpecimenPerPass);
            $diffClassManager->init($className, $specimensCode);
            $this->setDiffs($className, $diffClassManager->getStats());
            $this->setLonesomeRecords($className, $diffClassManager->getLonesomeRecords());
            $this->computeDiffs($className);
            unset($diffClassManager);
        }
    }

    private function setTaxons()
    {
        $flattenSpecimenCodes = [];
        array_walk_recursive($this->specimenCodes, function($a) use (&$flattenSpecimenCodes) {
            $flattenSpecimenCodes[] = $a;
        });

        $taxonRepository = $this->emR->getRepository('\AppBundle\Entity\Taxon');

        $arrayChunkSpecimenCodes = array_chunk($flattenSpecimenCodes, 300);
        if (count($arrayChunkSpecimenCodes)) {
            foreach ($arrayChunkSpecimenCodes as $chunkSpecimenCodes) {
                $taxons = $taxonRepository->findBestTaxonsBySpecimenCode($chunkSpecimenCodes);
                $this->taxons = array_merge($this->taxons, $taxons);
            }
        }
    }

    private function getTaxon($specimenCode)
    {
        if (isset($this->taxons[$specimenCode])) {
            return $this->taxons[$specimenCode];
        }
        return null;
    }

    /**
     * @param string $specimenCode
     */
    private function setTaxon($specimenCode)
    {
        if (!isset($this->diffs['datas'][$specimenCode]['taxon'])) {
            //$taxonRepository = $this->emR->getRepository('\AppBundle\Entity\Taxon');
            //$taxon = $taxonRepository->findBestTaxonsBySpecimenCode($specimenCode);
            //$this->diffs['datas'][$specimenCode]['taxon'] = $taxon instanceof Taxon ? $taxon->__toString() : '';
            $this->diffs['datas'][$specimenCode]['taxon'] = $this->getTaxon($specimenCode);
        }
    }

    /**
     * @param string $className
     */
    private function computeDiffs($className)
    {
        $this->stats[$className] = [];
        if (isset($this->diffs['classes'][$className])) {
            foreach ($this->diffs['classes'][$className] as $specimenCode => $rows) {
                $this->setTaxon($specimenCode);
                if (!isset($this->diffs['datas'][$specimenCode])) {
                    $this->diffs['datas'][$specimenCode] = [];
                    $this->diffs['datas'][$specimenCode]['classes'] = [];
                }
                if (!isset($this->diffs['datas'][$specimenCode]['classes'][$className])) {
                    $this->diffs['datas'][$specimenCode]['classes'][$className] = [];
                }
                foreach ($rows as $recordId => $fields) {
                    $this->setStatsForClass($className, $fields);
                    $this->diffs['datas'][$specimenCode]['classes'][$className]['fields'] = $fields;
                    $this->diffs['datas'][$specimenCode]['classes'][$className]['id'] = $recordId;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getAllStats()
    {
        return $this->stats;
    }

    /**
     * @param string $className
     * @param array  $fields
     */
    private function setStatsForClass($className, $fields)
    {
        $fieldsName = array_keys($fields);
        foreach ($fieldsName as $fieldName) {
            if (!isset($this->stats[$className][$fieldName])) {
                $this->stats[$className][$fieldName] = 0;
            }
            $this->stats[$className][$fieldName]++;
        }
    }

    /**
     * @param string|null $className
     * @return array
     */
    public function getDiffs($className = null)
    {
        if (null !== $className) {
            return $this->diffs[$className];
        }
        return $this->diffs;
    }

    /**
     * @return array
     */
    public function getLonesomeRecords()
    {
        return $this->lonesomeRecords;
    }

    /**
     * @return array
     */
    public function getStatsLonesomeRecords()
    {
        return $this->statsLonesomeRecords;
    }

    /**
     * Set les enregistrements orphelins
     * @param string $className
     * @param array  $lonesomeRecords
     */
    public function setLonesomeRecords($className, $lonesomeRecords)
    {
        $this->lonesomeRecords[$className] = [];
        foreach ($lonesomeRecords as $db => $items) {
            if (!isset($this->lonesomeRecords[$className][$db])) {
                $this->lonesomeRecords[$className][$db] = [];
            }
            $specimenCodesNewSpecimenRecords = [];
            if ($className != 'Specimen') {
                $specimenCodesNewSpecimenRecords = array_column($this->lonesomeRecords['Specimen'][$db],
                    'specimenCode');
            }

            foreach ($items as $lonesomeRecord) {
                if ($className == 'Specimen' || !in_array($lonesomeRecord['specimenCode'],
                        $specimenCodesNewSpecimenRecords)
                ) {
                    $this->lonesomeRecords[$className][$db][] = $lonesomeRecord;
                    if (!isset($this->statsLonesomeRecords[$lonesomeRecord['specimenCode']])) {
                        $this->statsLonesomeRecords[$lonesomeRecord['specimenCode']] = [];
                    }
                    $this->statsLonesomeRecords[$lonesomeRecord['specimenCode']][] = [
                        'class' => $className,
                        'id' => $lonesomeRecord['id'],
                        'db' => $db
                    ];
                }
            }
        }

    }

    /**
     * @param string $className
     * @param array  $stats
     */
    public function setDiffs($className, $stats)
    {
        $this->diffs['classes'][$className] = $stats;
        $this->classes[$className] = array_keys($stats);
    }

    /**
     * @return array
     */
    public function getAllSpecimensId()
    {
        return array_keys($this->diffs['datas']);
    }

}
