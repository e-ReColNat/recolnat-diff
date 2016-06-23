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
        if (!is_null($selectedClassesNames) && is_string($selectedClassesNames) && $selectedClassesNames != 'all') {
            $validClassesName = [$selectedClassesNames];
            array_walk($validClassesName, function(&$className) {
                $className = ucfirst(strtolower($className));
            });
        }

        $lonesomeRecords = $this->getData();
        $returnLonesomes = [];

        if (count($lonesomeRecords)) {
            foreach ($lonesomeRecords as $catalogNumber => $records) {
                $return = null;
                foreach ($records as $record) {
                    $flag = true;
                    if (!is_null($db) && $keyRef[$db] != $record['db']) {
                        $flag = false;
                    }
                    if ($flag && empty($validClassesName)) {
                        $return[] = $record;
                    }
                    if (!empty($validClassesName) && in_array($record['class'], $validClassesName) && $flag) {
                        $return[] = $record;
                    }
                }
                if (!is_null($return)) {
                    $returnLonesomes[$catalogNumber] = $return;
                }
            }
        }

        return $returnLonesomes;
    }

    /**
     * retourne les nouveaux enregistrements pour des catalog numbers et une base
     * @param array      $catalogNumbers
     * @param null|string $db
     * @return array
     */
    public function getLonesomeRecordsForCatalogNumbers($catalogNumbers=[], $db = null)
    {
        $lonesomeRecords = [];
        if (count($catalogNumbers)) {
            foreach ($catalogNumbers as $catalogNumber) {
                if (isset($this->getData()[$catalogNumber])) {
                    $lonesomeRecords[$catalogNumber] = $this->getData()[$catalogNumber];
                }
            }
        }
        if (count($lonesomeRecords)) {
            if (!is_null($db)) {
                $lonesomeRecords = array_filter($lonesomeRecords, function($el) use ($db) {
                    return $el['db'] == $db;
                });
            }

        }

        return $lonesomeRecords;
    }

    /**
     * Retourne les nouveaux enregistrements pour une base
     * @param null|string $className
     * @param string      $db
     * @return array
     */
    public function getLonesomeRecordsOrderedByCatalogNumbers($db, $className = null)
    {
        if (!is_null($className)) {
            $className = ucfirst(strtolower($className));
        }

        return array_filter($this->getData(), function($items) use ($db, $className) {
            $itemsFiltered = array_filter($items, function($item) use ($db, $className) {
                if (is_null($className)) {
                    return $item['db'] == $db;
                } else {
                    return $item['db'] == $db && $item['class'] == $className;
                }
            });

            return count($itemsFiltered) > 0;
        });
    }
}
