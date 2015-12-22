<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of CsvExporter
 *
 * @author tpateffoz
 */
class CsvExporter
{

    public $datas;
    private $exportPath;
    /* @var $files \ArrayObject */
    private $files;

    public function __construct($datas, $exportPath)
    {
        $this->datas = $datas;
        $this->exportPath = $exportPath;
    }

    public function generateFiles()
    {
        foreach ($this->datas as $className => $datasPerClass) {
            $entityExporterConstructor = '\\AppBundle\\Business\\Exporter\\'.ucfirst($className).'Exporter';
            /* @var $entityExporter \AppBundle\Business\Exporter\AbstractEntityExporter */
            $entityExporter = new $entityExporterConstructor();
            $fileExport = new \Symfony\Component\Filesystem\Filesystem();
            $fileName = $this->exportPath . '/' . strtolower($className) . '.csv';
            $fileExport->touch($fileName);
            $fileExport->chmod($fileName, 0777);

            $fp = fopen($fileName, 'w');

            $writeHeaders = true;
            $datasPerClass = $this->filterDatas($datasPerClass, $entityExporter, $className);
            foreach ($datasPerClass as $rows) {
                if ($writeHeaders) {
                    $fieldsName = array_keys($rows);
                    // Pour enlever les underscores en fin du nom du champ si besoin
                    foreach ($fieldsName as $key => $value) {
                        if (substr($value, -1) == '_') {
                            $fieldsName[$key] = substr($value, 0, -1);
                        }
                    }
                    fputcsv($fp, $fieldsName, "\t");
                    $writeHeaders = false;
                }
                fputcsv($fp, array_values($rows), "\t");
            }
            fclose($fp);
            $this->files[$className]=new \SplFileObject($fileName) ;
        }
    }

    public function getFiles()
    {
        return $this->files;
    }

    private function filterDatas($datas, $entityExporter, $className)
    {
        $filteredDatas = [];
        if (count($datas) > 0) {
            $acceptedFieldsName = [];
            $fieldsName = array_keys($datas[0]);
            foreach ($fieldsName as $fieldName) {
                if ($entityExporter->exportToCsv($fieldName)) {
                    $acceptedFieldsName[] = $fieldName;
                }
            }
            if (count($acceptedFieldsName) > 0) {
                foreach ($datas as $key => $row) {
                    foreach ($acceptedFieldsName as $fieldName) {
                        isset($row[$fieldName]) ? $filteredDatas[$key][$fieldName] = $row[$fieldName] : $filteredDatas[$key][$fieldName] = null;
                            
                        /*try {
                            $filteredDatas[$key][$fieldName] = $row[$fieldName];
                        }
                        catch(\Exception $e) {
                            var_dump($row);
                            echo sprintf('%s non trouvé dans %s<br />', $fieldName, $className);
                        }*/
                    }
                }
            }
        }
        return $filteredDatas;
    }

}
