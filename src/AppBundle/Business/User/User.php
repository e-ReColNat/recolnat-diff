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
    private $prefs ;
    private $exportPath ;
    private $maxItemPerPage ;
    
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
        $this->institutionCode = $institutionCode ;
    }
    
    public function getPrefs()
    {
        if (empty($this->prefs)) {
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            $prefsFile = $this->getDataDirPath().'prefs.json' ;
            if (!$fs->exists($prefsFile)) {
                $this->createPrefsFile($prefsFile) ;
            }
            $handle = fopen($prefsFile, "r") ;
            $this->prefs = json_decode(fread($handle, filesize($prefsFile)), true);
        }
        return $this->prefs;
    }

    private function createPrefsFile($prefsFile)
    {
        $handle = fopen($prefsFile, "w") ;
        fwrite($handle, json_encode($this->setPrefs(), JSON_PRETTY_PRINT));
        fclose($handle) ;
        chmod($prefsFile, 0755) ;
    }
    
    private function setPrefs()
    {
        return [
            "dwc" => [
	    "csvDelimiter" => ";",
	    "csvEnclosure" => "",
	    "csvLineBreak" => "\\n"
	    ],
            "csv" => [
	    "csvDelimiter" => ";",
	    "csvEnclosure" => "",
	    "csvLineBreak" => "\\n"
	    ],
            "preferedExport"  => "dwc"
        ];
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
