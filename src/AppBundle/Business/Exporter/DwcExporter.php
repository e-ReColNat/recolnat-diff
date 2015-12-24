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
    public $entitiesName=[
            'Specimen',     
            'Bibliography',
            'Determination',
            'Recolte',
            'Multimedia'
        ];
    protected $formattedDatas=[];

    public function formatDatas()
    {
        $formatDatas=[];
        foreach ($this->datas as $key=>$data) {
            $formatDatas[$key]=[];
            $occurrenceid=$data['Specimen']['occurrenceid'];
            $formatDatas[$key]['Specimen']=array_merge($data['Specimen'], $data['Stratigraphy']) ;
            
            if (count($data['Determination'])>0) {
                foreach ($data['Determination'] as $key2=>$determination) {
                    $formatDatas[$key]['Determination'][$key2]=array_merge($determination, $data['Taxon'][$determination['identificationid']]) ;
                }
            }
            
            if (count($data['Bibliography'])>0) {
                foreach ($data['Bibliography'] as $key2=>$bibliography) {
                    $formatDatas[$key]['Bibliography'][$key2]= ['occurrenceid'=>$occurrenceid] + $bibliography ;
                }
            }
            
            if (count($data['Multimedia'])>0) {
                foreach ($data['Multimedia'] as $key2=>$multimedia) {
                    $formatDatas[$key]['Multimedia'][$key2]= ['occurrenceid'=>$occurrenceid] + $multimedia ;
                }
            }
            $formatDatas[$key]['Recolte']=array_merge($data['Recolte'], $data['Localisation']) ;
            $formatDatas[$key]['Recolte']['occurrenceid']= $occurrenceid;

        }
        return $formatDatas;
    }
     
    public function generate() 
    {
        $this->formattedDatas = $this->formatDatas() ;
        $csvExporter = new CsvExporter($this->formattedDatas, $this->getExportDirPath()) ;
        $csvExporter->generateForDwc() ;
        $this->csvFiles = $csvExporter->getFiles();
        
        $fileExport = new \Symfony\Component\Filesystem\Filesystem() ;
        $fileName = $this->getExportDirPath().'/meta.xml' ;
        $fileExport->touch($fileName) ;
        $fileExport->chmod($fileName, 0777);
        file_put_contents($fileName, $this->generateXmlMeta()) ;
        
        return $this->createZipFile();
    }
    
    private function generateXmlMeta()
    {
        $classNames=['Specimen', 'Determination', 'Recolte', 'Multimedia', 'Bibliography'] ;
        $this->dwc = new \DOMDocument('1.0', 'UTF-8');
        $this->dwc->preserveWhiteSpace = false;
        $this->dwc->formatOutput = true;
        $root = $this->dwc->createElement('archive');
        $root->setAttribute('xmlns', 'http://rs.tdwg.org/dwc/text/');
        $this->dwc->appendChild($root);
        foreach ($classNames as $className) {
            $this->setXmlGenericEntity($root, $className);
        }
        return $this->dwc->saveXML($root);
    }

    private function createZipFile() {
        $fileExport = new \Symfony\Component\Filesystem\Filesystem() ;
        $zipFilePath = $this->getExportDirPath().'/dwc.zip' ;
        $arrayFilesName=[];
        $arrayFilesName[] = $this->getMetaFilepath().' ';
        foreach ($this->csvFiles as $csvFile) {
            $arrayFilesName[]=$csvFile->getPathName().' ';
        }
        
        $zipCommand = sprintf('zip -j %s %s', $zipFilePath, implode(' ', $arrayFilesName)) ;
        exec($zipCommand) ;
        $fileExport->chmod($zipFilePath, 0777);
       /*foreach (glob("*.csv") as $filename) {
            $filelist = $archive->add($filename, PCLZIP_OPT_REMOVE_PATH, $this->getExportDirPath());
        }*/
        /*$zip = new \ZipArchive;
        
        $res = $zip->open($zipFilePath, \ZipArchive::CREATE|\ZipArchive::OVERWRITE|\ZipArchive::CHECKCONS);
        if ($res === TRUE) {
            $options = array('add_path' => ' ','remove_all_path' => TRUE);
            $zip->addGlob($this->getExportDirPath().'/*.{csv,xml}', GLOB_BRACE, $options);
            $zip->close();
            $fileExport->chmod($zipFilePath, 0777);
        }
        else {
            throw new \Exception(sprintf('Echec lors de l\'ouverture de l\'archive %s', $res));
        }*/
        
        return $zipFilePath ;
    }
    private function getMetaFilepath() {
        return realpath($this->getExportDirPath().'/meta.xml') ;
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
    private function setXmlGenericEntity(\DOMElement &$root, $extension) {
        $entityExporter = $this->getEntityExporter($extension);
        $flagCore=false;
        if ($extension == $this->getCoreName()) {
            $coreNode = $this->dwc->createElement('core');
            $flagCore = true;
        }
        else {
            $coreNode = $this->dwc->createElement('extension');
        }
        $this->setCsvParameterNode($coreNode, $entityExporter->getNameSpace());
        $root->appendChild($coreNode);
        $this->setNodeFile($coreNode, $extension.'.csv');
        $compt = 0;
        $keys = $entityExporter->getKeysEntity();
        
        if ($extension == 'Specimen') {
            $stratigraphyExporter = new Entity\StratigraphyExporter();
            $stratigraphyKeys = $stratigraphyExporter->getKeysEntity() ;
            $stratigraphyKeys = $this->array_delete($stratigraphyKeys, 'occurrenceid') ;
            $stratigraphyKeys = $this->array_delete($stratigraphyKeys, 'geologicalcontextid') ;
            $keys = array_merge($keys, $stratigraphyKeys) ;
        }
        if ($extension == 'Recolte') {
            $localisationExporter = new Entity\LocalisationExporter();
            $localisationKeys = $localisationExporter->getKeysEntity();
            $localisationKeys = $this->array_delete($localisationKeys, 'locationid') ;
            $keys = array_merge($keys, $localisationKeys) ;
        }
        if ($extension == 'Determination') {
            $taxonExporter = new Entity\TaxonExporter();
            $taxonKeys = $taxonExporter->getKeysEntity() ;
            $taxonKeys = $this->array_delete($taxonKeys, 'taxonid') ;
            $keys = array_merge($keys, $taxonKeys) ;
        }
        foreach ($keys as $key => $fieldName) {
            if ($fieldName == $entityExporter->getCoreIdFieldName()) {
                $this->setIndexNode($coreNode, $key, $flagCore, $compt);
            }
            else {
                $term = $entityExporter->getXmlTerm($fieldName);
                $this->setFieldNode($coreNode, $compt, $term);
            }
        }
    }

    private function array_delete($array, $element) {
        return array_diff($array, [$element]);
    }
    private function getCoreName() {
        return 'Specimen';
    }
    
    private function setIndexNode(\DOMElement &$coreNode, $key, $flagCore, &$compt)
    {
        if ($flagCore) {
            $node = $this->dwc->createElement('id');
        }
        else {
            $node = $this->dwc->createElement('coreid');
        }
        $node->setAttribute('index', $key);
        $coreNode->appendChild($node);
        $compt++;
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
