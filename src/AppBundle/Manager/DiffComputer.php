<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

/**
 * Description of DiffStatsManager
 *
 * @author tpateffoz
 */
class DiffComputer
{

    private $catalogNumbers;

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

    /**
     * @var Collection
     */
    protected $collection;

    protected $diffs = [];
    protected $lonesomeRecords = [];
    protected $classes = [];
    protected $stats = [];
    protected $statsLonesomeRecords = [];
    protected $taxons = [];

    protected $logQueries = false;

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
        $this->emB = $managerRegistry->getManager('buffer');
        $this->diffs['datas'] = [];
        $this->diffs['classes'] = [];
        if (!$this->logQueries) {
            $this->emR->getConnection()->getConfiguration()->setSQLLogger(null);
            $this->emB->getConnection()->getConfiguration()->setSQLLogger(null);
        }
    }

    /**
     * @param Collection $collection
     * @param array      $catalogNumbers
     * @return $this
     */
    public function init(Collection $collection, $catalogNumbers)
    {
        $this->collection = $collection;
        $this->setCatalogNumbers($catalogNumbers);
        if (count($this->catalogNumbers) > 0) {
            foreach ($this->classOrder as $className) {
                $this->computeClassname($className);
            }
        }

        return $this;
    }

    /**
     * @param array $catalogNumbers
     */
    public function setCatalogNumbers($catalogNumbers)
    {
        $this->catalogNumbers = $catalogNumbers;
        $this->setTaxons();
    }

    /**
     * @param string $className
     * @throws \Exception
     */
    public function computeClassname($className)
    {
        if (isset($this->catalogNumbers[ucfirst($className)])) {
            $catalogNumbers = $this->catalogNumbers[$className];
            $nameDiffClassManager = '\\AppBundle\\Manager\\Diff'.ucfirst(strtolower($className));
            /* @var $diffClassManager \AppBundle\Manager\AbstractDiff */
            $diffClassManager = new $nameDiffClassManager($this->managerRegistry, $this->maxNbSpecimenPerPass);
            $diffClassManager->init($this->collection, $className, $catalogNumbers);
            $this->setDiffs($className, $diffClassManager->getStats());
            $this->setLonesomeRecords($className, $diffClassManager->getLonesomeRecords());
            $this->computeDiffs($className);
            unset($diffClassManager);
        } else {
            throw new \Exception('no catalognumber for '.$className);
        }
        $this->diffs['classes'] = $this->classes;
    }

    private function setTaxons($catalogNumbers = null, $base = 'recolnat')
    {
        if (is_null($catalogNumbers)) {
            $catalogNumbers = $this->catalogNumbers;
        }
        $flattenCatalogNumbers = [];
        array_walk_recursive($catalogNumbers, function($a) use (&$flattenCatalogNumbers) {
            $flattenCatalogNumbers[] = $a;
        });
        $em = $this->emR;
        if ($base != 'recolnat') {
            $em = $this->emB;
        }
        $taxonRepository = $em->getRepository('\AppBundle\Entity\Taxon');

        $arrayChunkCatalogNumbers = array_chunk($flattenCatalogNumbers, 300);
        if (count($arrayChunkCatalogNumbers)) {
            foreach ($arrayChunkCatalogNumbers as $chunkCatalogNumbers) {
                $taxons = $taxonRepository->findBestTaxonsByCatalogNumbers($this->collection, $chunkCatalogNumbers);
                $this->taxons = array_merge($this->taxons, $taxons);
            }
        }
    }

    private function getTaxon($catalogNumber)
    {
        if (isset($this->taxons[$catalogNumber])) {
            return $this->taxons[$catalogNumber];
        }

        return null;
    }

    public function getTaxons()
    {
        return $this->taxons;
    }
    /**
     * @param string $catalogNumber
     */
    private function setTaxon($catalogNumber)
    {
        if (!isset($this->diffs['datas'][$catalogNumber]['taxon'])) {
            $this->diffs['datas'][$catalogNumber]['taxon'] = $this->getTaxon($catalogNumber);
        }
    }

    /**
     * @param string $className
     */
    private function computeDiffs($className)
    {
        $this->stats[$className] = [];
        if (isset($this->diffs['classes'][$className])) {
            foreach ($this->diffs['classes'][$className] as $catalogNumber => $rows) {
                //$this->setTaxon($catalogNumber);
                if (!isset($this->diffs['datas'][$catalogNumber])) {
                    $this->diffs['datas'][$catalogNumber] = [];
                    $this->diffs['datas'][$catalogNumber][$className] = [];
                }
                foreach ($rows as $recordId => $fields) {
                    $this->setStatsForClass($className, $fields);
                    $this->diffs['datas'][$catalogNumber][$className]['fields'] = $fields;
                    $this->diffs['datas'][$catalogNumber][$className]['id'] = $recordId;
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
    private function setLonesomeRecords($className, $lonesomeRecords)
    {
        $this->lonesomeRecords[$className] = [];
        /*foreach ($lonesomeRecords as $db => $items) {
            $this->setTaxons(array_column($items, 'catalogNumber'), $db);

            foreach ($items as $lonesomeRecord) {
                if (!isset($this->lonesomeRecords[$lonesomeRecord['catalogNumber']])) {
                    $this->lonesomeRecords[$lonesomeRecord['catalogNumber']] = [];
                }

                if (!isset($this->lonesomeRecords[$lonesomeRecord['catalogNumber']][$className])) {
                    $this->lonesomeRecords[$lonesomeRecord['catalogNumber']][$className] = [];
                }

                $this->lonesomeRecords[$lonesomeRecord['catalogNumber']][$className][] = [
                    'id' => $lonesomeRecord['id'],
                    'db' => $lonesomeRecord['db']
                ];

            }
        }*/
        foreach ($lonesomeRecords as $db => $items) {
            $this->setTaxons(array_column($items, 'catalogNumber'), $db);

            if (!isset($this->lonesomeRecords[$className][$db])) {
                $this->lonesomeRecords[$className][$db] = [];
            }

            foreach ($items as $lonesomeRecord) {
                $catalogNumber = $lonesomeRecord['catalogNumber'];

                $this->lonesomeRecords[$className][$db][] = $lonesomeRecord;
                if (!isset($this->statsLonesomeRecords[$catalogNumber])) {
                    $this->statsLonesomeRecords[$catalogNumber] = [];
                }
            }
        }
    }

    public static function computeStatsLonesomeRecords($lonesomeRecordsByClassName)
    {
        $stats = [];
        foreach ($lonesomeRecordsByClassName as $className => $lonesomeRecordsByDb) {
            foreach ($lonesomeRecordsByDb as $db => $lonesomeRecords) {
                foreach ($lonesomeRecords as $lonesomeRecord) {
                    $catalogNumber = $lonesomeRecord['catalogNumber'];
                    if (!isset($stats[$catalogNumber])) {
                        $stats[$catalogNumber] = [];
                    }

                    if (!isset($stats[$catalogNumber][$className])) {
                        $stats[$catalogNumber][$className] = [];
                    }
                    $stats[$catalogNumber][$className][] =
                        [
                            'id' => $lonesomeRecord['id'],
                            'db' => $db,
                        ];
                }
            }
        }

        return $stats;
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

    /**
     * @return array
     */
    public function getAllDatas()
    {
        return array_merge($this->getDiffs(),
            [
                'stats' => $this->getAllStats(),
                'lonesomeRecords' => $this->getLonesomeRecords(),
                'statsLonesomeRecords' => $this->getStatsLonesomeRecords()
            ]);
    }

    /**
     * @param Collection $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

}
