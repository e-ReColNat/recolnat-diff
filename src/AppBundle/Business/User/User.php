<?php

namespace AppBundle\Business\User;

/**
 * Description of User
 *
 * @author tpateffoz
 */
class User
{

    private $institutionCode;
    /* @var $prefs \AppBundle\Business\User\Prefs */
    private $prefs;
    private $exportPath;
    private $maxItemPerPage;

    /**
     * 
     * @param string $export_path
     * @return \AppBundle\Business\User\User
     */
    public function __construct($export_path, $maxItemPerPage)
    {
        $this->exportPath = $export_path;
        $this->maxItemPerPage = $maxItemPerPage;
        return $this;
    }

    public function init($institutionCode)
    {
        $this->institutionCode = $institutionCode;
        $this->createDir();
        $this->getPrefs();
        return $this;
    }

    private function createDir()
    {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        if (!$fs->exists($this->getDataDirPath())) {
            $fs->mkdir($this->getDataDirPath(), 0755);
        }
    }

    public function getPrefs()
    {
        $this->prefs = new Prefs();
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        
        if (!$fs->exists($this->getPrefsFileName())) {
            $this->createPrefsFile();
        }
        
        $handle = fopen($this->getPrefsFileName(), "r");
        $this->prefs->load(json_decode(fread($handle, filesize($this->getPrefsFileName())), true));
        return $this->prefs;
    }

    private function createPrefsFile()
    {
        $handle = fopen($this->getPrefsFileName(), "w");
        fwrite($handle, $this->prefs->toJson());
        fclose($handle);
        chmod($this->getPrefsFileName(), 0755);
    }

    public function savePrefs(Prefs $prefs)
    {
        $handle = fopen($this->getPrefsFileName(), "w");
        fwrite($handle, $prefs->toJson());
        fclose($handle);
        chmod($this->getPrefsFileName(), 0755);
    }

    public function getPrefsFileName() {
        return $this->getDataDirPath() . 'prefs.json';
    }
    public function getPrefsFile() {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        
        if (!$fs->exists($this->getPrefsFileName())) {
            $this->createPrefsFile($this->getPrefsFileName());
        }
        return $prefsFile ;
        
    }
    /**
     * 
     * @param String $institutionCode
     * @return String
     */
    public function getDataDirPath()
    {
        return realpath($this->exportPath) . '/' . $this->institutionCode . '/';
    }

    /**
     * 
     * @param String $institutionCode
     * @return String
     */
    public function getExportDirPath()
    {
        return $this->getDataDirPath() . $this->filename . '/export/';
    }

}
