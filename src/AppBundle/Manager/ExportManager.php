<?php

namespace AppBundle\Manager;

use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Manager\GenericEntityManager;
/**
 * Description of ExportManager
 *
 * @author tpateffoz
 */
class ExportManager
{

    private $exportPath;
    private $sessionManager;
    private $institutionCode;
    private $genericEntityManager;
    private $filename=null;

    /**
     * 
     * @param string $export_path
     * @param Session $sessionManager
     * @param GenericEntityManager $genericEntityManager
     * @return \AppBundle\Manager\ExportManager
     */
    public function __construct($export_path, Session $sessionManager, GenericEntityManager $genericEntityManager)
    {
        $this->exportPath = $export_path;
        $this->sessionManager = $sessionManager;
        $this->genericEntityManager = $genericEntityManager;
        return $this;
    }

    /**
     * 
     * @param String $institutionCode
     * @return \AppBundle\Manager\ExportManager
     */
    public function init($institutionCode, $filename)
    {
        $this->filename = $filename;
        $this->institutionCode = $institutionCode;
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        if (!$fs->exists($this->getChoicesDirPath())) {
            $fs->mkdir($this->getChoicesDirPath(), 0777);
        }
        if (!($this->sessionManager->has('choices')) && ($this->sessionManager->has('file') && $this->sessionManager->get('file') == $filename)) {
            $path = $this->getChoicesFileName();

            if ($fs->exists($path)) {
                $content = file_get_contents($path);
                $this->sessionManager->set('choices', json_decode($content, true));
            }
        }
        else {
            $this->sessionManager->set('file', $filename);
            $this->sessionManager->set('choices', []);
        }
        return $this;
    }

    public function getFiles() {
        $returnDirs=[];
        $institutionDir = $this->getChoicesDirPath() ;
        if ($handle = opendir($institutionDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && is_dir($institutionDir.$entry)) {
                    $returnDirs[] =$entry ;
                }
            }
            closedir($handle);
        }
        return $returnDirs;
    }
    /**
     * 
     * @param String $institutionCode
     * @return String
     */
    public function getChoicesDirPath()
    {
        return realpath($this->exportPath) . '/' . $this->institutionCode.'/' ;
    }
    /**
     * 
     * @param String $institutionCode
     * @return String
     */
    public function getChoicesFileName()
    {
        if(!is_null($this->filename)) {
            return $this->getChoicesDirPath($this->institutionCode) . $this->filename.'/session_' . $this->institutionCode . '.json';
        }
        return null ;
    }

    /**
     * 
     * @param array $choices
     */
    public function setChoices($choices)
    {
        foreach ($choices as $row) {
            $this->setChoice($row);
        }
    }

    /**
     * 
     * @param array $sessionChoices
     * @param array $row
     */
    public function setChoice($row)
    {
        $sessionChoices=[];
        if ($this->sessionManager->has('choices')) {
            $sessionChoices = $this->sessionManager->get('choices');
        }
        $flag = false;
        $row['data'] = $this->genericEntityManager->getData($row['choice'], $row['className'], $row['fieldName'], $row['relationId']);
        if (count($sessionChoices)>0) {
            foreach ($sessionChoices as $key=>$choice) {
                if (
                        $choice['className'] == $row['className'] &&
                        $choice['fieldName'] == $row['fieldName'] &&
                        $choice['relationId'] == $row['relationId'] 
                        ) {
                    $sessionChoices[$key] = $row ;
                    $flag = true;
                }
            }
        }
        
        if (!$flag) {
            $sessionChoices[] = $row;
        }
        $this->sessionManager->set('choices', $sessionChoices);
    }

    /**
     * 
     * @return array
     */
    public function getChoices()
    {
        return $this->sessionManager->get('choices');
    }

    //choices[class][relationId][fieldName] == value
    public function getChoicesForDisplay() 
    {
        $choices = $this->getChoices() ;
        $returnChoices = [] ;
        if (count($choices) >0 ) {
            foreach ($choices as $choice) {
                if (!isset($returnChoices[$choice['className']])) {
                    $returnChoices[$choice['className']] = [];
                }
                if (!isset($returnChoices[$choice['className']][$choice['relationId']])) {
                    $returnChoices[$choice['className']][$choice['relationId']] = [];
                }
                $returnChoices[$choice['className']][$choice['relationId']][$choice['fieldName']] = $choice['choice'];
            }
        }
        return $returnChoices;
    }
    
    public function getChoicesBySpecimenId() 
    {
        $choices = $this->getChoices() ;
        $returnChoices = array() ;
        if (count($choices) >0 ) {
            foreach ($choices as $choice) {
                if (!isset($returnChoices[$choice['specimenId']])) {
                    $returnChoices[$choice['specimenId']] = [];
                }
                unset($choice[$choice['specimenId']]);
                $returnChoices[$choice['specimenId']][] =$choice ;
            }
        }
        return $returnChoices;
    }
    public function saveChoices()
    {
        $fs = new \Symfony\Component\Filesystem\Filesystem() ;
        $fs->dumpFile($this->getChoicesFileName(), json_encode($this->getChoices(), JSON_PRETTY_PRINT)) ;
    }

}
