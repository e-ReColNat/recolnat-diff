<?php

namespace AppBundle\Business\Exporter;

use AppBundle\Business\Exporter\Entity\AbstractEntityExporter;
use AppBundle\Business\User\Prefs;
use AppBundle\Manager\UtilityService;

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
            mkdir($this->exportPath, 02774);
            chgrp($this->exportPath, UtilityService::getFileGroup($this->exportPath));
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


    /**
     * @param array  $data
     * @param string $occurrenceid
     * @return array
     */
    public function getMultimediaData($data, $occurrenceid)
    {
        $returnData = [];
        if (isset($data['Multimedia']) && count($data['Multimedia']) > 0) {
            foreach ($data['Multimedia'] as $key2 => $multimedia) {
                $returnData[$key2] = ['occurrenceid' => $occurrenceid] + $multimedia;
            }
        }

        return $returnData;
    }

    /**
     * @param array  $data
     * @param string $occurrenceid
     * @return array
     */
    public function getBibliographyData($data, $occurrenceid)
    {
        $returnData = [];
        if (isset($data['Bibliography']) && count($data['Bibliography']) > 0) {
            foreach ($data['Bibliography'] as $key2 => $bibliography) {
                $returnData[$key2] = ['occurrenceid' => $occurrenceid] + $bibliography;
            }
        }

        return $returnData;
    }

    /**
     * @param array  $data
     * @param string $occurrenceid
     * @return array
     */
    public function getDeterminationData($data, $occurrenceid)
    {
        $returnData = [];
        if (isset($data['Determination']) && count($data['Determination']) > 0) {
            foreach ($data['Determination'] as $key2 => $determination) {
                $taxon = $determination['Taxon'];
                $determination['occurrenceid'] = $occurrenceid;
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
    public function getSpecimenData($data)
    {
        return $data['Specimen'];
    }

    /**
     * @param array $data
     * @return array
     */
    public function getRecolteData($data)
    {
        return $data['Recolte'];
    }

    /**
     * remove csv & xml files
     * @param array $files
     */
    protected function removeFiles(array $files)
    {
        foreach ($files as $file) {
            exec('rm '.$file);
        }
    }
}
