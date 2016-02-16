<?php

namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

/**
 * Description of DiffStatsManager
 *
 * @author tpateffoz
 */
class DiffComputer
{

    private $arrayIds;

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
    protected $diffs = array();
    protected $lonesomeRecords = array();
    protected $classes = array();
    protected $stats = array();
    protected $statsLonesomeRecords = array();

    private $classOrder = [
        'Specimen',
        'Bibliography',
        'Determination',
        'Multimedia',
        'Recolte',
        'Stratigraphy',
        'Taxon'
    ];

    public function __construct(EntityManager $emR, EntityManager $emD)
    {
        $this->emR = $emR;
        $this->emD = $emD;
        $this->diffs['datas'] = [];
        $this->diffs['classes'] = [];
    }

    /**
     * @param array $arrayIds
     * @return $this
     */
    public function init($arrayIds)
    {
        $this->arrayIds = $arrayIds;
        if (count($this->arrayIds) > 0) {
            //foreach ($this->arrayIds as $className => $specimensCode) {
            foreach ($this->classOrder as $className) {
                if (isset($this->arrayIds[$className])) {
                    $specimensCode = $this->arrayIds[$className];
                    //foreach ($this->arrayIds[$className] as $specimensCode) {
                    $nameDiffClassManager = '\\AppBundle\\Manager\\Diff'.ucfirst(strtolower($className));
                    /* @var $diffClassManager \AppBundle\Manager\DiffAbstract */
                    $diffClassManager = new $nameDiffClassManager($this->emR, $this->emD);
                    $diffClassManager->init($className, $specimensCode);
                    $this->setDiffs($className, $diffClassManager->getStats());
                    $this->setLonesomeRecords($className, $diffClassManager->getLonesomeRecords());
                    $this->computeDiffs($className);
                    unset($diffClassManager);
                    //}
                }
            }
        }
        $this->diffs['classes'] = $this->classes;
        return $this;
    }

    /**
     * @param string $specimenCode
     */
    private function setTaxon($specimenCode)
    {
        if (!isset($this->diffs['datas'][$specimenCode]['display'])) {
            $taxonRepository = $this->emR->getRepository('\AppBundle\Entity\Taxon');
            $taxon = $taxonRepository->findBestTaxonsBySpecimenCode($specimenCode);
            $this->diffs['datas'][$specimenCode]['taxon'] = $taxon instanceof \AppBundle\Entity\Taxon ? $taxon->__toString() : '';
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
     * @return array
     */
    public function getDiffs()
    {
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
            $specimenCodesNewSpecimenRecords=[];
            if ($className != 'Specimen') {
                $specimenCodesNewSpecimenRecords = array_column($this->lonesomeRecords['Specimen'][$db],
                    'specimenCode');
            }

            foreach ($items as $lonesomeRecord) {
                if ($className == 'Specimen' || !in_array($lonesomeRecord['specimenCode'], $specimenCodesNewSpecimenRecords)
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
