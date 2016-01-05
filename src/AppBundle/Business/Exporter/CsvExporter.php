<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of CsvExporter
 *
 * @author tpateffoz
 */
class CsvExporter extends AbstractExporter
{
    /* @var $files \ArrayObject */

    private $files;

    public function formatDatas()
    {
        
    }

    public function generate()
    {
        $filesHandler = [];
        foreach ($this->datas as $key => $record) {
            foreach ($record as $className => $datasPerClass) {
                $entityExporter = $this->getEntityExporter($className);
                $writeHeaders = false;
                if (!isset($filesHandler[$className])) {
                    $this->createFile($className);
                    $filesHandler[$className] = fopen($this->files[$className]->getPathname(), 'w');
                    $writeHeaders = true;
                }
                if ($writeHeaders) {
                    $fieldsName = $entityExporter->getKeysEntity();
                    fputcsv($filesHandler[$className], $fieldsName, "\t");
                    $writeHeaders = false;
                }
                $filteredDatas = $this->filterDatas($datasPerClass, $entityExporter);
                fputcsv($filesHandler[$className], $filteredDatas, "\t");
            }
        }
        foreach ($filesHandler as $className => $fileHandler) {
            fclose($fileHandler);
        }
    }

    public function generateForDwc($delimiter, $enclosure, $lineBreak)
    {
        $filesHandler = [];
        $entitiesNameWithArray = [
            'Determination',
            'Multimedia',
            'Bibliography',
        ];
        foreach ($this->datas as $key => $record) {
            foreach ($record as $className => $datasPerClass) {
                /* @var $entityExporter AppBundle\Business\Exporter\Entity\AbstractEntityExporter */
                $entityExporter = $this->getEntityExporter($className);
                $writeHeaders = false;
                
                // Creation des fichiers
                if (!isset($filesHandler[$className])) {
                    $this->createFile($className);
                    $filesHandler[$className] = fopen($this->files[$className]->getPathname(), 'w');
                    $writeHeaders = true;
                }
                
                // Ecrit les entêtes en première ligne de csv
                if ($writeHeaders) {
                    if (in_array($className, $entitiesNameWithArray)) {
                        $fieldsName[$className] = array_keys(current($datasPerClass));
                    } else {
                        $fieldsName[$className] = array_keys($datasPerClass);
                    }
                    $this->writeToFile($filesHandler[$className], $fieldsName[$className], $delimiter, $enclosure, $lineBreak) ;
                    $writeHeaders = false;
                }
                
                if (in_array($className, $entitiesNameWithArray)) {
                    // Traitement des extensions qui peuvent avoir plusieurs enregistrements pour un specimen
                    foreach ($datasPerClass as $arrayRecord) {
                        $filteredDatas = $this->filterDatas($arrayRecord, $entityExporter, $fieldsName[$className]);
                        $this->writeToFile($filesHandler[$className], $filteredDatas, $delimiter, $enclosure, $lineBreak) ;
                    }
                } else {
                    // traitement des extensions n'ayant qu'un enregistrement par specimen
                    $filteredDatas = $this->filterDatas($datasPerClass, $entityExporter, $fieldsName[$className]);
                    $this->writeToFile($filesHandler[$className], $filteredDatas, $delimiter, $enclosure, $lineBreak) ;
                }
            }
        }
        foreach ($filesHandler as $className => $fileHandler) {
            fclose($fileHandler);
        }
    }

    /**
     * Ecrit une ligne dans le fichier et ajoute un retour à la ligne
     * @param resource $fileHandler
     * @param array $datas
     * @param string $lineBreak
     */
    private function writeToFile($fileHandler, $datas, $delimiter =";", $enclosure = '"', $lineBreak="\r\n", $encloseAll = false) 
    {
        $line =  $this->arrayToCsv($datas, $delimiter, $enclosure, $encloseAll) ;
        fwrite($fileHandler, $line);
        fwrite($fileHandler, $lineBreak);
    }
    
    /**
     * Crée un fichier csv
     * @param string $className
     * @param string $extension
     */
    private function createFile($className, $extension ='csv')
    {
        $fileExport = new \Symfony\Component\Filesystem\Filesystem();
        $fileName = $this->exportPath . '/' . strtolower($className) .'.'. $extension;
        $fileExport->touch($fileName);
        $fileExport->chmod($fileName, 0777);
        $this->files[$className] = new \SplFileObject($fileName);
    }

    public function getFiles()
    {
        return $this->files;
    }

    /**
     *  renvoie les données filtrées en fonction des champs acceptés
     * @param array $datas
     * @param Entity\AbstractEntityExporter $entityExporter
     * @param array $fieldsName
     * @return array
     */
    public function filterDatas($datas, $entityExporter, $fieldsName = [])
    {
        $filteredDatas = [];
        if (count($datas) > 0) {
            $acceptedFieldsName = [];
            if (empty($fieldsName)) {
                $fieldsName = $entityExporter->getKeysEntity();
            }
            foreach ($fieldsName as $fieldName) {
                if ($entityExporter->exportToCsv($fieldName)) {
                    $acceptedFieldsName[] = $fieldName;
                }
            }

            if (count($acceptedFieldsName) > 0) {
                foreach ($datas as $fieldName => $value) {
                    if (in_array($fieldName, $acceptedFieldsName)) {
                        $filteredDatas[$fieldName] = $datas[$fieldName];
                    }
                }
            }
        }
        return $filteredDatas;
    }

    /**
     * http://stackoverflow.com/questions/3933668/convert-array-into-csv
     * Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
     * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
     */
    private function arrayToCsv(array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false)
    {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');

        $output = array();
        foreach ($fields as $field) {
            if ($field === null && $nullToMysqlNull) {
                $output[] = 'NULL';
                continue;
            }

            // Enclose fields containing $delimiter, $enclosure or whitespace
            //if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
            if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc})/", $field)) {
                $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
            } else {
                $output[] = $field;
            }
        }

        return implode($delimiter, $output);
    }

}
