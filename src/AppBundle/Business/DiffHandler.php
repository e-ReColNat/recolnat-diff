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

    protected $institutionPath;
    protected $collectionCode;

    /**
     * DiffHandler constructor.
     * @param string $dirPath
     */
    public function __construct($dirPath)
    {
        $this->institutionPath = $dirPath;
    }

    /**
     * @param array $diffs
     */
    public function saveDiffs(array $diffs)
    {
        $this->setDiffsFile();
        $this->getDiffsFile()->save($diffs);
    }

    /**
     * @return bool
     */
    public function shouldSearchDiffs()
    {
        return !is_file($this->getCollectionPath().'/'.Diffs::DIFF_FILENAME);
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
    public function getCollectionCode()
    {
        return $this->collectionCode;
    }

    /**
     * @return string
     */
    public function getInstitutionPath()
    {
        $institutionPath = $this->institutionPath ;
        $this->createDir($institutionPath) ;
        return realpath($institutionPath);
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
        $this->choicesFile = new Choices($this->getCollectionPath());
        return $this;
    }

    /**
     * @return DiffHandler
     */
    public function setDiffsFile()
    {
        $this->diffs = new Diffs($this->getCollectionPath());
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
    public function getLonesomeRecordsIndexedBySpecimenCode($db, $selectedClassName = null)
    {
        return $this->getDiffsFile()->getLonesomeRecordsIndexedBySpecimenCode($db, $selectedClassName);
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

    /**
     * @return string
     */
    public function getCollectionPath()
    {
        $collectionPath = $this->getInstitutionPath().'/'.$this->collectionCode ;
        $this->createDir($collectionPath) ;

        return realpath($collectionPath);
    }

    /**
     * @param string $dir
     */
    private function createDir($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755);
        }
    }
}
