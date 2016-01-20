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
    protected $choicesFile ;
    /*
     * @var Diffs
     */
    protected $diffs ;
    
    protected $dirPath;
    protected $filename;
    
    public function __construct($dirPath, $filename)
    {
        $this->dirPath = $dirPath ;
        $this->filename = $filename ;
        $this->setChoicesFile();
        $this->setDiffsFile();
    }
    /**
     * Renvoie le timestamp de date de création ou presque ...
     * @return int
     */
    public function getCTime() {
        return $this->getDiffs()->getMTime();
    }
    /**
     * Renvoie le timestamp de date de modification
     * @return int
     */
    public function getMTime() {
        return $this->getChoices()->getMTime();
    }
    public function getFilename()
    {
        return $this->filename;
    }

    public function getDirPath() {
        return realpath($this->dirPath);
    }
    public function getPath() {
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

    public function setChoicesFile()
    {
        $this->choicesFile = new Choices($this->getPath());
        return $this;
    }

    public function setDiffsFile()
    {
        $this->diffs = new Diffs($this->getPath());
        return $this;
    }
}
