<?php

namespace AppBundle\Business;

use AppBundle\Entity\Collection;
use AppBundle\Manager\UtilityService;

/**
 * Description of DiffFiles
 *
 * @author tpateffoz
 */
class DiffHandler
{
    /*
     * @var Choices
     */
    protected $choicesFile;
    /*
     * @var Diffs
     */
    protected $diffsFile = null;

    protected $path;
    /** @var  Collection */
    protected $collection;

    protected $userGroup;

    /**
     * DiffHandler constructor.
     * @param string     $dirPath
     * @param string     $userGroup
     * @param Collection $collection
     */
    public function __construct($dirPath, Collection $collection, $userGroup)
    {
        $this->path = $dirPath;
        $this->collection = $collection;
        $this->userGroup = $userGroup;
    }

    /**
     * @param array $diffs
     */
    public function saveDiffs(array $diffs)
    {
        $this->getDiffsFile()->save($diffs);
    }

    /**
     * @return bool
     */
    public function shouldSearchDiffs()
    {
        return !is_file($this->getCollectionPath().Diffs::DIFF_FILENAME);
    }

    /**
     * Renvoie le timestamp de date de création ou presque ...
     * @return int
     */
    public function getCTime()
    {
        return $this->getDiffsFile()->getMTime();
    }

    /**
     * Renvoie le timestamp de date de modification
     * @return int
     */
    public function getMTime()
    {
        return $this->getChoicesFile()->getMTime();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return UtilityService::createDir($this->path, $this->userGroup);
    }

    /**
     *
     * @return Choices
     */
    public function getChoicesFile()
    {
        if (is_null($this->choicesFile)) {
            $this->setChoicesFile();
        }

        return $this->choicesFile;
    }

    /**
     *
     * @return Diffs
     */
    public function getDiffsFile()
    {
        if (is_null($this->diffsFile)) {
            $this->setDiffsFile();
        }

        return $this->diffsFile;
    }

    /**
     * @return DiffHandler
     */
    public function setChoicesFile()
    {
        $this->choicesFile = new Choices($this->getCollectionPath(), $this->userGroup);

        return $this;
    }

    /**
     * @return DiffHandler
     */
    public function setDiffsFile()
    {
        $this->diffsFile = new Diffs($this->getCollectionPath(), $this->userGroup);

        return $this;
    }

    /**
     * @param string|null       $db
     * @param string|array|null $selectedClassesNames
     * @return array
     */
    public function getLonesomeRecords($db = null, $selectedClassesNames = null)
    {
        return $this->getDiffsFile()->getLonesomeRecords($db, $selectedClassesNames);
    }

    /**
     * @param string      $db
     * @param null|string $selectedClassName
     * @return array
     */
    public function getLonesomeRecordsIndexedByCatalogNumber($db, $selectedClassName = null)
    {
        return $this->getDiffsFile()->getLonesomeRecordsIndexedByCatalogNumber($db, $selectedClassName);
    }

    /**
     * @param string      $db
     * @param null|string $selectedClassName
     * @return array
     */
    public function getLonesomeRecordsOrderedByCatalogNumbers($db, $selectedClassName = null)
    {
        return $this->getDiffsFile()->getLonesomeRecordsOrderedByCatalogNumbers($db, $selectedClassName);
    }


    /**
     * @param $items
     * @param $diffs
     * @param $inputClassesName
     * @param $inputOrigin
     * @param $choices
     * @return array
     */
    public static function formatItemsToChoices($items, $diffs, $inputClassesName, $inputOrigin, $choices)
    {
        if (count($items) > 0) {
            foreach ($items as $catalogNumber => $row) {
                foreach ($row['classes'] as $className => $data) {
                    $rowClass = $diffs['datas'][$catalogNumber]['classes'][$className];
                    $relationId = $rowClass['id'];
                    foreach ($rowClass['fields'] as $fieldName => $rowFields) {
                        $doUpdate = false;
                        if (in_array(strtolower($className), $inputClassesName)) {
                            $doUpdate = true;
                        }
                        if ($doUpdate) {
                            $choices[] = [
                                'className' => $className,
                                'fieldName' => $fieldName,
                                'relationId' => $relationId,
                                'choice' => $inputOrigin,
                                'catalogNumber' => $catalogNumber,
                            ];
                        }
                    }
                }
            }

            return $choices;
        }

        return $choices;
    }

    /**
     * @return string
     */
    public function getCollectionPath()
    {
        $institutionPath = $this->getPath().'/'.$this->collection->getInstitution()->getInstitutioncode().'/';
        UtilityService::createDir($institutionPath, $this->userGroup);

        $collectionPath = $institutionPath.$this->collection->getCollectioncode();

        return UtilityService::createDir($collectionPath, $this->userGroup);
    }

    /**
     * @param string $search
     * @return array
     */
    public function search($search)
    {
        $catalogNumbers = $this->searchByCatalogNumber($search);
        $catalogNumbers = array_merge($this->searchByTaxon($search), $catalogNumbers);

        return $catalogNumbers;
    }

    /**
     * @param string $search
     * @return array
     */
    public function searchByTaxon($search)
    {
        $catalogNumbers = [];
        $search = strtolower($search);
        $filteredData = array_filter($this->getDiffsFile()->getData()['datas'], function($el) use ($search) {
            return (strpos(strtolower($el['taxon']), $search) !== false);
        });
        if (!empty($filteredData)) {
            $catalogNumbers = array_keys($filteredData);
        }

        return $catalogNumbers;
    }

    /**
     * @param string $search
     * @return array
     */
    public function searchByCatalogNumber($search)
    {
        $catalogNumbers = [];
        $regExpSearch = sprintf('/%s/i', $search);

        $results = preg_grep($regExpSearch, array_keys($this->getDiffsFile()->getData()['datas']));

        if (!empty($results)) {
            $catalogNumbers = $results;
        }

        return $catalogNumbers;
    }
}
