<?php

namespace AppBundle\Business;

use AppBundle\Manager\AbstractDiff;
use AppBundle\Manager\UtilityService;

class LonesomeRecords extends AbstractFile
{
    public $searchDiffs;
    const FILENAME = '/lonesomerecords.json';

    /**
     * @param string $dirPath
     * @param string $userGroup
     */
    public function __construct($dirPath, $userGroup)
    {
        $this->searchDiffs = false;
        $path = UtilityService::createFile($dirPath.self::FILENAME, $userGroup);
        parent::__construct($path, 'c+');
    }

    /**
     * @param string|null       $db
     * @param string|array|null $selectedClassesNames
     * @return array
     */
    public function getLonesomeRecords($db = null, $selectedClassesNames = null)
    {
        $keyRef = AbstractDiff::getKeysRef();
        $validClassesName = [];
        if (!is_null($selectedClassesNames) && is_string($selectedClassesNames) && $selectedClassesNames !== 'all') {
            $validClassesName = [$selectedClassesNames];
            array_walk($validClassesName, function(&$className) {
                $className = ucfirst(strtolower($className));
            });
        }

        $lonesomeRecords = $this->getData();
        $returnLonesomes = [];

        if (count($lonesomeRecords)) {
            foreach ($lonesomeRecords as $catalogNumber => $classesInLonesomeRecords) {
                $return = null;
                foreach ($classesInLonesomeRecords as $className => $records) {
                    foreach ($records as $record) {
                        $flag = true;
                        if (!is_null($db) && $keyRef[$db] != $record['db']) {
                            $flag = false;
                        }
                        if ($flag && empty($validClassesName)) {
                            $return[] = $record;
                        }
                        if (!empty($validClassesName) && in_array($className, $validClassesName) && $flag) {
                            $return[] = $record;
                        }
                    }
                    if (!is_null($return)) {
                        $returnLonesomes[$catalogNumber][$className] = $return;
                    }
                }
            }
        }

        return $returnLonesomes;
    }

    /**
     * retourne les nouveaux enregistrements pour des catalog numbers et une base
     * @param array $catalogNumbers
     * @return array
     */
    public function getLonesomeRecordsByCatalogNumbers($catalogNumbers = [])
    {
        $lonesomeRecords = [];
        if (count($catalogNumbers)) {
            foreach ($catalogNumbers as $catalogNumber) {
                if (isset($this->getData()[$catalogNumber])) {
                    $lonesomeRecords[$catalogNumber] = $this->getData()[$catalogNumber];
                }
            }
        }

        return $lonesomeRecords;
    }

    /**
     * Retourne les nouveaux enregistrements pour une base
     * @param string $db
     * @return array
     */
    public function getLonesomeRecordsByBase($db)
    {
        $keyRef = AbstractDiff::getKeysRef();
        $db = $keyRef[$db];
        $lonesomeRecords = $this->getData() ;

        foreach ($lonesomeRecords as $catalogNumber => $classesWithItems) {
            $filteredLonesomeRecords = array_filter($classesWithItems, function($items) use ($db) {
                $itemsFiltered = array_filter($items, function($item) use ($db) {
                    return $item['db'] == $db;
                });

                return count($itemsFiltered) > 0;
            });
            if (count($filteredLonesomeRecords)) {
                $lonesomeRecords[$catalogNumber] = $filteredLonesomeRecords;
            }
            else {
                unset($lonesomeRecords[$catalogNumber]);
            }
        }
        return $lonesomeRecords;
    }
}
