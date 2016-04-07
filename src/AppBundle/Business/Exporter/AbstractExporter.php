<?php

namespace AppBundle\Business\Exporter;

use AppBundle\Business\Exporter\Entity\AbstractEntityExporter;
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
     * @param array  $datas
     * @param string $exportPath
     */
    public function __construct($datas, $exportPath)
    {
        $this->datas = $datas;
        $this->exportPath = $exportPath;
        if (!is_dir($this->exportPath)) {
            mkdir($this->exportPath);
        }
        foreach ($this->entitiesName as $className) {
            $entityExporterConstructor = '\\AppBundle\\Business\\Exporter\\Entity\\'.ucfirst($className).'Exporter';
            /* @var $entityExporter \AppBundle\Business\Exporter\Entity\AbstractEntityExporter */
            $this->arrayEntityExport[$className] = new $entityExporterConstructor();
        }
    }

    /**
     *
     * @param string $className
     * @return AbstractEntityExporter
     */
    public function getEntityExporter($className)
    {
        return $this->arrayEntityExport[$className];
    }

    /**
     * @return string
     */
    public function getExportDirPath()
    {
        return realpath($this->exportPath);
    }

    /**
     * @param array $array
     * @param mixed $element
     * @return array
     */
    public function arrayDelete($array, $element)
    {
        return array_diff($array, [$element]);
    }

    /**
     *
     * @param mixed  $value
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
