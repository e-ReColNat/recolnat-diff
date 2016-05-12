<?php

namespace AppBundle\Business\Exporter;

use AppBundle\Business\User\Prefs;
use AppBundle\Entity\Localisation;
use AppBundle\Entity\Recolte;
use AppBundle\Entity\Stratigraphy;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Description of DwcExporter
 *
 * @author tpateffoz
 */
class DwcExporter extends AbstractExporter
{
    /* @var $dwc \DOMDocument */
    protected $dwc;
    protected $csvFiles;
    public $entitiesName = [
        'Specimen',
        'Bibliography',
        'Determination',
        'Recolte',
        'Multimedia'
    ];
    protected $formattedDatas = [];
    protected $dwcDelimiter = "\t";
    protected $dwcEnclosure = '"';
    protected $dwcLineBreak = "\t";
    protected $dwcIgnoreHeaderLines = true;
    protected $dwcDateFormat = 'Y-m-d';
    protected $zipFileName = 'dwc.zip';

    private $arrayEmptyClasses = [];

    /**
     * @return array
     */
    public function formatDatas()
    {
        $formattedData = [];
        $this->setEmptyClasses();


        foreach ($this->datas as $key => $data) {
            $formattedData[$key] = [];
            $occurrenceid = $data['Specimen']['occurrenceid'];

            $formattedData[$key]['Specimen'] = $this->getSpecimenData($data);

            $determinationData = $this->getDeterminationData($data);
            if (!empty($determinationData)) {
                $formattedData[$key]['Determination'] = $determinationData;
            }

            $bibliographyData = $this->getBibliographyData($data, $occurrenceid);
            if (!empty($bibliographyData)) {
                $formattedData[$key]['Bibliography'] = $bibliographyData;
            }

            $multimediaData = $this->getMultimediaData($data, $occurrenceid);
            if (!empty($multimediaData)) {
                $formattedData[$key]['Multimedia'] = $multimediaData;
            }

            $recolteData = $this->getRecolteData($data);
            if (!empty($recolteData)) {
                $formattedData[$key]['Recolte'] = $recolteData;
            }
        }

        return $formattedData;
    }

    /**
     * @param array $data
     * @param string $occurrenceid
     * @return array
     */
    private function getMultimediaData($data, $occurrenceid) {
        $returnData = [];
        if (isset($data['Multimedia']) && count($data['Multimedia']) > 0) {
            foreach ($data['Multimedia'] as $key2 => $bibliography) {
                $returnData[$key2] = ['occurrenceid' => $occurrenceid] + $bibliography;
            }
        }
        return $returnData;
    }

    /**
     * @param array $data
     * @param string $occurrenceid
     * @return array
     */
    private function getBibliographyData($data, $occurrenceid) {
        $returnData = [];
        if (isset($data['Bibliography']) && count($data['Bibliography']) > 0) {
            foreach ($data['Bibliography'] as $key2 => $bibliography) {
                $returnData[$key2] = ['occurrenceid' => $occurrenceid] + $bibliography;
            }
        }
        return $returnData;
    }
    /**
     * @param array $data
     * @return array
     */
    private function getDeterminationData($data)
    {
        $returnData = [];
        if (isset($data['Determination']) && count($data['Determination']) > 0) {
            foreach ($data['Determination'] as $key2 => $determination) {
                $taxon = $determination['Taxon'];
                unset($determination['Taxon']);
                if (is_array($taxon)) {
                    $returnData[$key2] = array_merge($determination, $taxon);
                } else {
                    $returnData[$key2] = $determination;
                }
            }
        }

        return $returnData;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getSpecimenData($data)
    {
        if (!isset($data['Stratigraphy']) || count($data['Stratigraphy']) == 0) {
            $data['Stratigraphy'] = $this->arrayEmptyClasses['Stratigraphy'];
        }

        return array_merge($data['Specimen'], $data['Stratigraphy']);
    }

    /**
     * @param array $data
     * @return array
     */
    private function getRecolteData($data)
    {
        $returnData = [];
        if (isset($data['Recolte']) && count($data['Recolte']) > 0) {
            if (!isset($data['Localisation']) || count($data['Localisation']) == 0) {
                $data['Localisation'] = $this->arrayEmptyClasses['Localisation'];
            }
            $returnData = array_merge($data['Recolte'], $data['Localisation']);
        }

        return $returnData;
    }

    /**
     * @param Prefs $prefs
     * @param array $options
     * @return string
     */
    public function generate(Prefs $prefs, array $options = [])
    {
        $this->setDwcDelimiter($prefs->getDwcDelimiter());
        $this->setDwcEnclosure($prefs->getDwcEnclosure());
        $this->setDwcLineBreak($prefs->getDwcLineBreak());
        $this->setDwcDateFormat($prefs->getDwcDateFormat());
        $this->formattedDatas = $this->formatDatas();
        $csvExporter = new CsvExporter($this->formattedDatas, $this->getExportDirPath());
        $this->csvFiles = $csvExporter->generate($prefs, ['dwc' => true]);

        $fileExport = new Filesystem();
        $fileName = $this->getExportDirPath().'/meta.xml';
        $fileExport->touch($fileName);
        file_put_contents($fileName, $this->generateXmlMeta());

        return $this->createZipFile();
    }

    /**
     * @return string
     */
    private function generateXmlMeta()
    {
        $this->dwc = new \DOMDocument('1.0', 'UTF-8');
        $this->dwc->preserveWhiteSpace = false;
        $this->dwc->formatOutput = true;
        $root = $this->dwc->createElement('archive');
        $root->setAttribute('xmlns', 'http://rs.tdwg.org/dwc/text/');
        $this->dwc->appendChild($root);
        foreach ($this->entitiesName as $className) {
            $this->setXmlGenericEntity($root, $className);
        }

        return $this->dwc->saveXML($root);
    }

    /*
     * @return string
     * @throws \Exception
     */
    private function createZipFile()
    {
        $zipFilePath = $this->getExportDirPath().'/'.$this->zipFileName;
        $arrayFilesName = [];
        $arrayFilesName[] = $this->getMetaFilepath().' ';
        if (is_array($this->csvFiles) && count($this->csvFiles) > 0) {
            foreach ($this->csvFiles as $csvFile) {
                /** @var \SplFileObject $csvFile */
                $arrayFilesName[] = $csvFile->getPathName().' ';
            }

            $zipCommand = sprintf('zip -j %s %s', $zipFilePath, implode(' ', $arrayFilesName));
            exec($zipCommand);
        } else {
            throw new \Exception('DWC-a can\'t be created !');
        }

        if (!is_file($zipFilePath)) {
            throw new \Exception('Zip file has not been created');
        }

        return $zipFilePath;
    }

    /**
     * @return string
     */
    private function getMetaFilepath()
    {
        return realpath($this->getExportDirPath().'/meta.xml');
    }

    /**
     *
     * @param \DOMElement $node
     * @param string      $rowType
     */
    private function setCsvParameterNode(\DOMElement&$node, $rowType)
    {
        $node->setAttribute('encoding', 'UTF-8');
        $node->setAttribute('fieldsTerminatedBy', $this->getDwcDelimiter());
        $node->setAttribute('linesTerminatedBy', $this->getDwcLineBreak());
        $node->setAttribute('fieldsEnclosedBy', $this->getDwcEnclosure());
        $node->setAttribute('ignoreHeaderLines', $this->getDwcIgnoreHeaderLines());
        $node->setAttribute('rowType', $rowType);
        $node->setAttribute('dateFormat', $this->getDwcDateFormat());
    }

    /**
     *
     * @param \DOMElement $coreNode
     * @param string      $filename
     */
    private function setNodeFile(\DOMElement&$coreNode, $filename)
    {
        $fileNode = $this->dwc->createElement('files');
        $locationNode = $this->dwc->createElement('location', strtolower($filename));
        $fileNode->appendChild($locationNode);
        $coreNode->appendChild($fileNode);
    }

    /**
     *
     * @param \DOMElement $root
     * @param string      $extension
     */
    private function setXmlGenericEntity(\DOMElement&$root, $extension)
    {
        $entityExporter = $this->getEntityExporter($extension);
        $flagCore = false;
        if ($extension == $this->getCoreName()) {
            $coreNode = $this->dwc->createElement('core');
            $flagCore = true;
        } else {
            $coreNode = $this->dwc->createElement('extension');
        }
        $this->setCsvParameterNode($coreNode, $entityExporter->getNameSpace());
        $root->appendChild($coreNode);
        $this->setNodeFile($coreNode, $extension.'.csv');
        $compt = 0;
        $keys = $entityExporter->getKeysEntity();
        foreach ($keys as $key => $fieldName) {
            if ($fieldName == $entityExporter->getCoreIdFieldName()) {
                $this->setIndexNode($coreNode, $key, $flagCore, $compt);
            } else {
                $term = $entityExporter->getXmlTerm($fieldName);
                $this->setFieldNode($coreNode, $compt, $term);
            }
        }
    }

    /**
     * @return string
     */
    private function getCoreName()
    {
        return 'Specimen';
    }

    /**
     * @param \DOMElement $coreNode
     * @param string      $key
     * @param boolean     $flagCore
     * @param int         $compt
     */
    private function setIndexNode(\DOMElement&$coreNode, $key, $flagCore, &$compt)
    {
        if ($flagCore) {
            $node = $this->dwc->createElement('id');
        } else {
            $node = $this->dwc->createElement('coreid');
        }
        $node->setAttribute('index', $key);
        $coreNode->appendChild($node);
        $compt++;
    }

    /**
     * @param \DOMElement $coreNode
     * @param int         $compt
     * @param string      $term
     */
    private function setFieldNode(\DOMElement&$coreNode, &$compt, $term = '')
    {
        if ($term != '') {
            $node = $this->dwc->createElement('field');
            $node->setAttribute('index', $compt);
            $node->setAttribute('term', $term);
            $coreNode->appendChild($node);
            $compt++;
        }
    }

    /**
     * @return string
     */
    public function getDwcDelimiter()
    {
        return $this->dwcDelimiter;
    }

    /**
     * @return string
     */
    public function getDwcEnclosure()
    {
        return $this->dwcEnclosure;
    }

    /**
     * @return string
     */
    public function getDwcLineBreak()
    {
        return $this->dwcLineBreak;
    }

    /**
     * @return boolean
     */
    public function getDwcIgnoreHeaderLines()
    {
        return $this->dwcIgnoreHeaderLines;
    }

    /**
     * @param string $dwcDelimiter
     */
    public function setDwcDelimiter($dwcDelimiter)
    {
        $this->dwcDelimiter = $dwcDelimiter;
    }

    /**
     * @param string $dwcEnclosure
     */
    public function setDwcEnclosure($dwcEnclosure)
    {
        $this->dwcEnclosure = $dwcEnclosure;
    }

    /**
     * @param string $dwcLineBreak
     */
    public function setDwcLineBreak($dwcLineBreak)
    {
        $this->dwcLineBreak = $dwcLineBreak;
    }

    /**
     * @param boolean $dwcIgnoreHeaderLines
     */
    public function setDwcIgnoreHeaderLines($dwcIgnoreHeaderLines)
    {
        $this->dwcIgnoreHeaderLines = $dwcIgnoreHeaderLines;
    }

    /**
     * @return string
     */
    public function getDwcDateFormat()
    {
        $search = ['d', 'm', 'Y', 'H', 'i', 's', '\T'];
        $replace = ['DD', 'MM', 'YYYY', 'hh', 'mm', 'ss', 'T'];

        return str_replace($search, $replace, $this->dwcDateFormat);
    }

    /**
     * @param string $dwcDateFormat
     */
    public function setDwcDateFormat($dwcDateFormat)
    {
        $this->dwcDateFormat = $dwcDateFormat;
    }

    private function setEmptyClasses()
    {
        $emptyStratigraphy = new Stratigraphy();
        $this->arrayEmptyClasses['Stratigraphy'] = $emptyStratigraphy->toArray();

        $emptyRecolte = new Recolte();
        $this->arrayEmptyClasses['Recolte'] = $emptyRecolte->toArray();

        $emptyLocalisation = new Localisation();
        $this->arrayEmptyClasses['Localisation'] = $emptyLocalisation->toArray();
    }
}
