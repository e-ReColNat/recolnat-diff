<?php
namespace Recolnat\DarwinCoreBundle\Component;
use Symfony\Component\HttpFoundation\File\File;

class DwcDiff
{
    /**
     * @var Extractor $core1
     */
    public $extractor1;
    /**
     * @var Extractor $core2
     */
    public $extractor2;
    /**
     * @var Extractor $extractor
     */
    private $extractor;
    /**
     * @var File $file1
     */
    private $file1;
    /**
     * @var File $file2
     */
    private $file2;
    
    /**
     * @var DarwinCoreArchive $diffDarwinArchive
     */
    public $diffDarwinArchiveHtml;
    
    /**
     * @var DarwinCoreArchive $diffDarwinArchive
     */
    public $diffDarwinArchiveText;
    
    /**
     * @var array $recordPresentInDwc
     */
    public $rowPresentInDwc ;
    /**
     * 
     * @var array $opCodes
     */
    public $opCodes;
    
    /**
     * array of boolean 
     * @var array
     */
    public $change;
    public function __construct(Extractor $extractor)
    {
        $this->extractor = $extractor;
        $this->rowPresentInDwc=array();
    }
    
    public function init(File $file1, File $file2)
    {
        $this->file1 = $file1;
        $this->file2 = $file2;
        $cloneExtractor = clone $this->extractor ;
        $this->extractor1 = $this->extractor->init($file1);
        $this->extractor2 = $cloneExtractor->init($file2);
        
        return $this;
    }
    
    public function compareFile() 
    {
        $core1 = $this->extractor1->getCore()->getData();
        $core2 = $this->extractor2->getCore()->getData();
        $this->setDiffDarwinArchiveHtml();
        foreach ($core1 as $coreId => $row) {
            foreach ($row->getDatas() as $key => $value) {
                $core2TmpData = '' ;
                if ($this->isRowPresentInFile2($coreId)) {
                    $core2TmpData = $core2[$coreId]->data[$key] ;
                }
                $diff = $this->setDiff($coreId, $key, $value, $core2TmpData);
            }
        }
        foreach ($core2 as $coreId => $row) {
            if (!$this->isRowPresentInFile1($coreId)) {
                foreach ($row->getDatas() as $key => $value) {
                    $diff = $this->setDiff($coreId, $key, '', $value);
                }
            }
        }
    }
    
    private function isRowPresentInFile1($id) {
        return $this->rowPresentInDwc[$id]['file1'] ;
    }
    
    private function isRowPresentInFile2($id) {
        return $this->rowPresentInDwc[$id]['file2'] ;
    }
    private function setDiffDarwinArchiveHtml() 
    {
            $core1 = $this->extractor1->getCore()->getData();
            $core2 = $this->extractor2->getCore()->getData();
            $this->diffDarwinArchiveHtml = $this->extractor1->darwinCoreArchive;
            
            foreach ($core1 as $id => $row) {
                $this->rowPresentInDwc[$id]['file1'] = true;
                $this->rowPresentInDwc[$id]['file2'] = false;
                if (array_key_exists($id, $core2)) {
                    $this->rowPresentInDwc[$id]['file2'] = true;
                }
            }
            foreach ($core2 as $id => $row) {
                if (!array_key_exists($id, $core1)) {
                    $this->rowPresentInDwc[$id]['file2'] = true;
                    $this->rowPresentInDwc[$id]['file1'] = false;
                    $this->diffDarwinArchiveHtml->core->data[$id] = $row ;
                }
        }
    }
    public function getDiff($format = 'html')
    {
        if (empty($this->diffDarwinArchiveHtml->core->data)) {
            $this->compareFile();
        }
        
        if ($format != 'html'){
            return $this->diffDarwinArchiveText;
        }
        return $this->diffDarwinArchiveHtml;
    }
    
    private function setDiff($id, $name, $text1, $text2) 
    {
        $this->change[$id][$name] = false;
        if ($text1 !== $text2)  {
            $this->change[$id][$name] = true;
        }
        $this->opCodes[$id][$name]= \FineDiff::getDiffOpcodes($text1, $text2);
        $resultHtml =  \FineDiff::renderDiffToHTMLFromOpcodes($text1, $this->opCodes[$id][$name]);
        $resultText =  \FineDiff::renderToTextFromOpcodes($text1, $this->opCodes[$id][$name]);
        $this->diffDarwinArchiveHtml->core->data[$id]->data[$name] = $resultHtml;
        $this->diffDarwinArchiveText->core->data[$id]->data[$name] = $resultText;
    }
    
    public function rowHasChanges($id) {
        if (isset($this->change[$id])) {
            foreach ($this->change[$id] as $fieldChange) {
                if ($fieldChange) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    public function recordHasChange($id, $name) {
        if (isset($this->change[$id]) && isset($this->change[$id][$name])) {
            return $this->change[$id][$name] ;
        }
        return FALSE ;
    }
}