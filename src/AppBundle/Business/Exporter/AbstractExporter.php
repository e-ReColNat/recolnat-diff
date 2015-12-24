<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of Exporter
 *
 * @author tpateffoz
 */
abstract class AbstractExporter
{
    public $exportPath;
    public $datas;
    public $entitiesName=[
            'Specimen',     
            'Bibliography',
            'Determination',
            'Localisation',
            'Recolte',
            'Stratigraphy',
            'Taxon',
            'Multimedia'
        ];
    
    public $arrayEntityExport=[];
    /**
     * 
     * @param array $datas
     * @param string $exportPath
     */
    public function __construct($datas, $exportPath) {
        $this->datas = $datas;
        $this->exportPath = $exportPath;
        foreach ($this->entitiesName as $className) {
            $entityExporterConstructor = '\\AppBundle\\Business\\Exporter\\Entity\\'.ucfirst($className).'Exporter';
            /* @var $entityExporter \AppBundle\Business\Exporter\AbstractEntityExporter */
            $this->arrayEntityExport[$className] = new $entityExporterConstructor();
        }
    }
    
    /**
     * 
     * @param string $className
     * @return AbstractEntityExporter
     */
    public function getEntityExporter($className) {
        return $this->arrayEntityExport[$className];
    }
    
    public function getExportDirPath() {
        return realpath($this->exportPath);
    }
    abstract public function generate();
    abstract public function formatDatas();
    
}
