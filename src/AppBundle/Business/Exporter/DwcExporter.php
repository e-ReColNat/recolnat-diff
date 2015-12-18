<?php

namespace AppBundle\Business\Exporter;

use AppBundle\Business\Exporter\AbstractExporter;

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


    public function generate() {
        $csvExporter = new CsvExporter($this->datas, $this->getExportDirPath()) ;
        $csvExporter->generateFiles() ;
        $this->csvFiles = $csvExporter->getFiles();
        
        $fileExport = new \Symfony\Component\Filesystem\Filesystem() ;
        $fileName = $this->getExportDirPath().'/meta.xml' ;
        $fileExport->touch($fileName) ;
        $fileExport->chmod($fileName, 0777);
        file_put_contents($fileName, $this->generateXmlMeta()) ;
        
        return $this->createZipFile();
    }
    
    public function generateXmlMeta()
    {
        $this->dwc = new \DOMDocument('1.0', 'UTF-8');
        $this->dwc->preserveWhiteSpace = false;
        $this->dwc->formatOutput = true;
        $root = $this->dwc->createElement('archive');
        $root->setAttribute('xmlns', 'http://rs.tdwg.org/dwc/text/');
        $this->dwc->appendChild($root);
        foreach ($this->datas as $className => $row) {
            $this->setXmlGenericEntity($root, $className, array_keys($row[0]));
        }
        return $this->dwc->saveXML($root);
    }

    private function createZipFile() {
        //var_dump($this->csvFiles);
        
        $fileExport = new \Symfony\Component\Filesystem\Filesystem() ;
        $zip = new \ZipArchive;
        $zipFilePath = $this->getExportDirPath().'/dwc.zip' ;
        $res = $zip->open($zipFilePath, \ZipArchive::CREATE|\ZipArchive::OVERWRITE|\ZipArchive::CHECKCONS);
        if ($res === TRUE) {
            $options = array('add_path' => ' ','remove_all_path' => TRUE);
            $zip->addGlob($this->getExportDirPath().'/*.{csv,xml}', GLOB_BRACE, $options);
            $zip->close();
            $fileExport->chmod($zipFilePath, 0777);
        }
        else {
            throw new \Exception(sprintf('Echec lors de l\'ouverture de l\'archive %s', $res));
        }
        return $zipFilePath ;
    }
    /**
     * 
     * @param \DOMElement $node
     * @param type $rowType
     */
    private function setCsvParameterNode(\DOMElement &$node, $rowType)
    {
        $node->setAttribute('encoding', 'UTF-8');
        $node->setAttribute('fieldsTerminatedBy', '\t');
        $node->setAttribute('linesTerminatedBy', '\n');
        $node->setAttribute('fieldsEnclosedBy', '');
        $node->setAttribute('ignoreHeaderLines', '1');
        $node->setAttribute('rowType', $rowType);
    }

    /**
     * 
     * @param \DOMElement $coreNode
     * @param type $filename
     */
    private function setNodeFile(\DOMElement &$coreNode, $filename)
    {
        $fileNode = $this->dwc->createElement('files');
        $locationNode = $this->dwc->createElement('location', strtolower($filename));
        $fileNode->appendChild($locationNode) ;
        $coreNode->appendChild($fileNode);
    }

    /**
     * 
     * @param \DOMElement $root
     * @param type $extension
     * @param type $keys
     */
    private function setXmlGenericEntity(\DOMElement &$root, $extension, $keys) {
        $entityExporterConstructor = '\\AppBundle\\Business\\Exporter\\'.ucfirst($extension).'Exporter';
        /* @var $entityExporter \AppBundle\Business\Exporter\AbstractEntityExporter */
        $entityExporter = new $entityExporterConstructor();
        if ($extension == $this->getCoreName()) {
            $coreNode = $this->dwc->createElement('core');
        }
        else {
            $coreNode = $this->dwc->createElement('extension');
        }
        $this->setCsvParameterNode($coreNode, $entityExporter->getNameSpace());
        $root->appendChild($coreNode);
        $this->setNodeFile($coreNode, $extension.'.csv');
        $compt = 0;
        foreach ($keys as $key => $fieldName) {
            if ($fieldName == $entityExporter->getIdFieldName()) {
                $this->setIndexNode($coreNode, $key);
            }
            $term = $entityExporter->getXmlTerm($fieldName);
            $this->setFieldNode($coreNode, $compt, $term);
        }
    }

    private function getCoreName() {
        return 'Specimen';
    }
    
    private function setIndexNode(\DOMElement &$coreNode, $key)
    {
        $node = $this->dwc->createElement('id');
        $node->setAttribute('index', $key);
        $coreNode->appendChild($node);
    }
    private function setFieldNode(\DOMElement &$coreNode, &$compt, $term = '')
    {
        if ($term != '') {
            $node = $this->dwc->createElement('field');
            $node->setAttribute('index', $compt);
            $node->setAttribute('term', $term);
            $coreNode->appendChild($node);
            $compt++;
        }
    }

}
