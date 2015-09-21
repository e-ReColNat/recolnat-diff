<?php

namespace Recolnat\DarwinCoreBundle\Component;

use Recolnat\DarwinCoreBundle\Component\DarwinCoreArchive;

class DwcDiff
{

    /**
     * @var DarwinCoreArchive $diffDarwinArchive
     */
    public $dwc1;

    /**
     * @var DarwinCoreArchive $diffDarwinArchive
     */
    public $dwc2;

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
    public $rowPresentInDwc;

    /**
     * 
     * @var array $opCodes
     */
    public $opCodes;

    /**
     * array of boolean 
     * @var array
     */
    public $changes;

    public function __construct(DarwinCoreArchive $dwc1, DarwinCoreArchive $dwc2)
    {
        $this->rowPresentInDwc = array();
        $this->dwc1 = $dwc1;
        $this->dwc2 = $dwc2;
    }

    public function compareFile()
    {
        $core1 = $this->dwc1->getCore();
        $core2 = $this->dwc2->getCore();
        $this->setDiffDarwinArchive();
        foreach ($core1->getDatasWithIndexes() as $coreId => $data) {
            foreach ($data as $key => $value) {
                $core2TmpData = '';
                if ($this->isRowPresentInFile2($coreId)) {
                    $core2TmpData = $core2->getData($coreId, $key);
                }
                $this->setDiff($coreId, $core1->getNumericIndex($key), $value, $core2TmpData);
            }
        }
        foreach ($core2->getDatasWithIndexes() as $coreId => $data) {
            if (!$this->isRowPresentInFile1($coreId)) {
                foreach ($data as $key => $value) {
                    $this->setDiff($coreId, $core1->getNumericIndex($key), '', $value);
                }
            }
        }
    }

    private function isRowPresentInFile1($id)
    {
        return isset($this->rowPresentInDwc[$id]['dwc1']);
    }

    private function isRowPresentInFile2($id)
    {
        return isset($this->rowPresentInDwc[$id]['dwc2']);
    }

    private function setDiffDarwinArchive()
    {
        $core1 = $this->dwc1->getCore();
        $core2 = $this->dwc2->getCore();
        $this->diffDarwinArchiveHtml = clone $this->dwc1;
        $this->diffDarwinArchiveText = clone $this->dwc1;

        foreach ($core1->getData() as $id => $row) {
            $this->rowPresentInDwc[$id]['dwc1'] = true;
            $this->rowPresentInDwc[$id]['dwc2'] = false;
            if (array_key_exists($id, $core2->getData())) {
                $this->rowPresentInDwc[$id]['dwc2'] = true;
            }
        }
        foreach ($core2->getData() as $id => $row) {
            if (!array_key_exists($id, $core1->getData())) {
                $this->rowPresentInDwc[$id]['dwc2'] = true;
                $this->rowPresentInDwc[$id]['dwc1'] = false;
                $this->diffDarwinArchiveHtml->core->data[$id] = $row;
            }
        }
    }

    public function getDiff($format = 'html')
    {
        if (empty($this->diffDarwinArchiveHtml->core->data)) {
            $this->compareFile();
        }

        if ($format != 'html') {
            return $this->diffDarwinArchiveText;
        }
        return $this->diffDarwinArchiveHtml;
    }

    private function setDiff($id, $key, $text1, $text2)
    {
        $this->changes[$id][$key] = false;
        if ($text1 !== $text2) {
            $this->changes[$id][$key] = true;
        }
            $this->opCodes[$id][$key] = \FineDiff::getDiffOpcodes($text1, $text2);
            $resultHtml = \FineDiff::renderDiffToHTMLFromOpcodes($text1, $this->opCodes[$id][$key]);
            $resultText = \FineDiff::renderToTextFromOpcodes($text1, $this->opCodes[$id][$key]);
            $this->diffDarwinArchiveHtml->core->data[$id][$key] = $resultHtml;
            $this->diffDarwinArchiveText->core->data[$id][$key] = $resultText;
    }

    public function rowHasChanges($id)
    {
        if (isset($this->changes[$id])) {
            foreach ($this->changes[$id] as $fieldChange) {
                if ($fieldChange) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function recordHasChange($id, $key)
    {
        $key = $this->dwc1->core->getNumericIndex($key) ;
        if (isset($this->changes[$id]) && isset($this->changes[$id][$key])) {
            return $this->changes[$id][$key];
        }
        return FALSE;
    }

    public function getOpCodes()
    {
        return $this->opCodes;
    }

    public function getChanges()
    {
        return $this->changes;
    }
}
