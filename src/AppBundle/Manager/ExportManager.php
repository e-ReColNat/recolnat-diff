<?php

namespace AppBundle\Manager;

use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Manager\GenericEntityManager;
use AppBundle\Business\DiffHandler;
use Symfony\Component\HttpFoundation\Request ;
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
    protected $diffManager;
    protected $maxItemPerPage;
    
    /** @var \AppBundle\Business\DiffHandler */
    private $diffHandler;

    /**
     * 
     * @param string $export_path
     * @param Session $sessionManager
     * @param GenericEntityManager $genericEntityManager
     * @param \AppBundle\Manager\DiffManager
     * @return \AppBundle\Manager\ExportManager
     */
    public function __construct($export_path, Session $sessionManager, GenericEntityManager $genericEntityManager, DiffManager $diffManager, $maxItemPerPage)
    {
        $this->exportPath = $export_path;
        $this->sessionManager = $sessionManager;
        $this->genericEntityManager = $genericEntityManager;
        $this->diffManager = $diffManager;
        $this->maxItemPerPage = $maxItemPerPage;
        return $this;
    }

    /**
     * 
     * @param String $institutionCode
     * @return \AppBundle\Manager\ExportManager
     */
    public function init($institutionCode, $filename = null)
    {
        $this->institutionCode = $institutionCode;
        
        if (!is_null($filename)) {
            $this->filename = $filename;
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            if (!$fs->exists($this->getChoicesDirPath())) {
                $fs->mkdir($this->getChoicesDirPath(), 0777);
            }
            
            $this->diffHandler = new DiffHandler($this->getChoicesDirPath(), $this->filename);
            
            $doReload = false;
            $path = $this->diffHandler->getChoices()->getFilename();
            if ($this->diffHandler->getDiffs()->generateDiff) {
                $results =$this->diffManager->init($institutionCode);
                $this->sessionManager->set('diffs', $results['diffs']);
                $this->sessionManager->set('specimensCode', $results['specimensCode']);
                $this->sessionManager->set('stats', $results['stats']);
                $this->diffHandler->getDiffs()->saveDiffs($results['diffs'], $results['stats'], $results['specimensCode']);
                $this->diffHandler->getDiffs()->generateDiff = false;
            }
            else {
                //$start = microtime(true) ;
                $results =$this->diffHandler->getDiffs()->getData();
                //var_dump(microtime(true) - $start);
                $this->sessionManager->set('diffs', $results['diffs']);
                $this->sessionManager->set('specimensCode', $results['specimensCode']);
                $this->sessionManager->set('stats', $results['stats']);
            }
            
            if (!($this->sessionManager->has('file') || $this->sessionManager->get('file') != $filename)) {
                $doReload=true;
            }
            if (!($this->sessionManager->has('choices') ) || empty($this->sessionManager->get('choices'))) {
                $doReload=true;
            }
            if ($doReload && $fs->exists($path)) {
                $this->sessionManager->set('choices', $this->diffHandler->getChoices()->getContent());
            }
            else {
                $this->sessionManager->set('file', $filename);
            }
        }
        return $this;
    }

    public function getSpecimenIdsAndDiffsAndStats(Request $request, $selectedClassName=null, $specimensWithChoices=[], $choicesToRemove=[])
    {
        $session = $this->sessionManager;
        $className=[];
        if (is_string($selectedClassName)) {$className=[$selectedClassName];}
        if (is_array($selectedClassName)) {$className=$selectedClassName;}

        if (!is_null($request->query->get('reset', null))) {
            $session->clear();
        }
        $stats = $this->sessionManager->get('stats') ;
        $stats = $this->diffHandler->getDiffs()->filterResults($stats, $className, $specimensWithChoices, $choicesToRemove) ;
        $session->set('stats', $stats);
        return [$session->get('specimensCode'), $session->get('diffs'), $stats];
    }
    
    public function getMaxItemPerPage(Request $request) {
        $session = $this->sessionManager;
        
        $requestMaxItem=$request->get('maxItemPerPage', null);
        if (!is_null($requestMaxItem)) {
            $session->set('maxItemPerPage', (int) $requestMaxItem);
        }
        elseif (!$session->has('maxItemPerPage')) {
            $session->set('maxItemPerPage', $this->maxItemPerPage);
        }
        return $session->get('maxItemPerPage') ;
    }
    
    public function getFiles() 
    {
        $returnDirs=[];
        $institutionDir = $this->getChoicesDirPath() ;
        if ($handle = opendir($institutionDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && is_dir($institutionDir.$entry)) {
                    $returnDirs[] =new \AppBundle\Business\DiffHandler($institutionDir, $entry) ;
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
            return $this->diffHandler->getChoices()->getPathname();
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
        $this->diffHandler->getChoices()->save($sessionChoices) ;
    }

    /**
     * 
     * @return array
     */
    public function getChoices()
    {
        if ($this->sessionManager->has('choices')) {
            return $this->sessionManager->get('choices');
        }
        return [];
    }

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
        $this->diffHandler->getChoices()->save($this->getChoices());
    }

}
