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
    protected $diffs;

    protected $dirPath;
    protected $filename;

    /**
     * DiffHandler constructor.
     * @param string $dirPath
     * @param string $filename
     */
    public function __construct($dirPath, $filename)
    {
        $this->dirPath = $dirPath;
        $this->filename = $filename;
        $this->setChoicesFile();
        $this->setDiffsFile();
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
    public function getFilename()
    {
        return $this->filename;
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
        return realpath($this->dirPath.'/'.$this->filename);
    }

    /**
     *
     * @return Choices
     */
    public function getChoices()
    {
        return $this->choicesFile;
    }

    /**
     *
     * @return Diffs
     */
    public function getDiffs()
    {
        return $this->diffs;
    }

    /**
     * @return $this
     */
    public function setChoicesFile()
    {
        $this->choicesFile = new Choices($this->getPath());
        return $this;
    }

    /**
     * @return $this
     */
    public function setDiffsFile()
    {
        $this->diffs = new Diffs($this->getPath());
        return $this;
    }

    /**
     * @param string            $db
     * @param string|array|null $selectedClassesNames
     * @return array
     */
    public function getLonesomeRecords($db = null, $selectedClassesNames = null)
    {
        return $this->getDiffs()->getLonesomeRecords($db, $selectedClassesNames);
    }

    /**
     * @param string     $db
     * @param null|array $selectedClassesNames
     * @return array
     */
    public function getLonesomeRecordsIndexedBySpecimenCode($db, $selectedClassesNames = null)
    {
        return $this->getDiffs()->getLonesomeRecordsIndexedBySpecimenCode($db, $selectedClassesNames);
    }
}
