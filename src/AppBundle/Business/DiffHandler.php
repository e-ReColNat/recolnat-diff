<?php

namespace AppBundle\Business;

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
    protected $diffs = null;

    protected $dirPath;
    protected $collectionCode;

    /**
     * DiffHandler constructor.
     * @param string $dirPath
     */
    public function __construct($dirPath)
    {
        $this->dirPath = $dirPath;
    }

    /**
     * @param array $diffs
     */
    public function saveDiffs(array $diffs)
    {
        $this->setDiffsFile();
        $this->getDiffs()->save($diffs);
    }

    /**
     * @return bool
     */
    public function shouldSearchDiffs()
    {
        return !is_file($this->getPath().'/'.Diffs::DIFF_FILENAME);
    }

    /**
     * Renvoie le timestamp de date de création ou presque ...
     * @return int
     */
    public function getCTime()
    {
        return $this->getDiffs()->getMTime();
    }

    /**
     * Renvoie le timestamp de date de modification
     * @return int
     */
    public function getMTime()
    {
        return $this->getChoices()->getMTime();
    }

    /**
     * @return string
     */
    public function getCollectionCode()
    {
        return $this->collectionCode;
    }

    /**
     * @return string
     */
    public function getDirPath()
    {
        return realpath($this->dirPath);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return realpath($this->dirPath.'/'.$this->collectionCode);
    }

    /**
     *
     * @return Choices
     */
    public function getChoices()
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
    public function getDiffs()
    {
        if (is_null($this->diffs)) {
            $this->setDiffsFile();
        }
        return $this->diffs;
    }

    /**
     * @return DiffHandler
     */
    public function setChoicesFile()
    {
        $this->choicesFile = new Choices($this->getPath());
        return $this;
    }

    /**
     * @return DiffHandler
     */
    public function setDiffsFile()
    {
        $this->diffs = new Diffs($this->getPath());
        return $this;
    }

    /**
     * @param string|null       $db
     * @param string|array|null $selectedClassesNames
     * @return array
     */
    public function getLonesomeRecords($db = null, $selectedClassesNames = null)
    {
        return $this->getDiffs()->getLonesomeRecords($db, $selectedClassesNames);
    }

    /**
     * @param string      $db
     * @param null|string $selectedClassName
     * @return array
     */
    public function getLonesomeRecordsIndexedBySpecimenCode($db, $selectedClassName = null)
    {
        return $this->getDiffs()->getLonesomeRecordsIndexedBySpecimenCode($db, $selectedClassName);
    }

    /**
     * @param $collectionCode
     * @return DiffHandler
     */
    public function setCollectionCode($collectionCode)
    {
        $this->collectionCode = $collectionCode;
        return $this;
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
            foreach ($items as $specimenCode => $row) {
                foreach ($row['classes'] as $className => $data) {
                    $rowClass = $diffs['datas'][$specimenCode]['classes'][$className];
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
                                'specimenCode' => $specimenCode,
                            ];
                        }
                    }
                }
            }
            return $choices;
        }
        return $choices;
    }
}
