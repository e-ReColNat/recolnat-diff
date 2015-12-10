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
    protected $diffsFile ;
    
    protected $dirPath;
    protected $filename;
    
    public function __construct($dirPath, $filename)
    {
        $this->dirPath = $dirPath ;
        $this->filename = $filename ;
        $this->setChoicesFile();
        $this->setDiffsFile();
    }
    
    public function getFilename()
    {
        return $this->filename;
    }

    public function getPath() {
        return $this->dirPath.'/'.$this->filename;
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
        return $this->diffsFile;
    }

    public function setChoicesFile()
    {
        $this->choicesFile = new Choices($this->getPath());
        return $this;
    }

    public function setDiffsFile()
    {
        $this->diffsFile = new Diffs($this->getPath());
        return $this;
    }



}
