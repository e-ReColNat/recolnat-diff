<?php

namespace AppBundle\Business\Exporter;

use AppBundle\Business\User\Prefs;
/**
 * Description of Exporter
 *
 * @author tpateffoz
 */
abstract class AbstractExporter
{
    public $exportPath;
    public $exportPrefs;
    public $datas;
    public $entitiesName = [
            'Specimen',     
            'Bibliography',
            'Determination',
            'Localisation',
            'Recolte',
            'Stratigraphy',
            'Taxon',
            'Multimedia'
        ];
    
    public $arrayEntityExport = [];
    /**
     * @param array $datas
     * @param string $exportPath
     * @param ExportPrefs $exportPrefs
     */
    public function __construct($datas, $exportPath, ExportPrefs $exportPrefs) {
        $this->datas = $datas;
        $this->exportPath = $exportPath;
        $this->exportPrefs = $exportPrefs;
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
    
    public function array_delete($array, $element) {
        return array_diff($array, [$element]);
    }
    
    /**
     * 
     * @param mixed $value
     * @param string $dateFormat
     * @return mixed Time
     */
    public static function convertField($value, $dateFormat)
    {
        if ($value instanceof \DateTime) {
            return $value->format($dateFormat);
        }
        return $value;
    }
    
    abstract public function generate(Prefs $prefs, array $options = []);
    abstract public function formatDatas();
    
}
