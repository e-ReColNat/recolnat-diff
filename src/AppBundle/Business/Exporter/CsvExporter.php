<?php

namespace AppBundle\Business\Exporter;

use AppBundle\Business\User\Prefs;

/**
 * Description of CsvExporter
 *
 * @author tpateffoz
 */
class CsvExporter extends AbstractExporter
{
    /* @var $files \ArrayObject */

    private $files;
    protected $csvDelimiter;
    protected $csvEnclosure;
    protected $csvLineBreak;
    protected $csvDateFormat;

    private $fieldsName = [];
    private $acceptedFieldsName = [];
    public function formatDatas()
    {
        
    }

    /**
     * @param Prefs $prefs
     * @param array $options
     * @return \ArrayObject|string
     */
    public function generate(Prefs $prefs, array $options = [])
    {
        if (isset($options['dwc']) && $options['dwc'] == true) {
            $this->setCsvDelimiter($prefs->getDwcDelimiter());
            $this->setCsvEnclosure($prefs->getDwcEnclosure());
            $this->setCsvLineBreak($prefs->getDwcLineBreak());
            $this->setCsvDateFormat($prefs->getDwcDateFormat());
        } else {
            $this->setCsvDelimiter($prefs->getCsvDelimiter());
            $this->setCsvEnclosure($prefs->getCsvEnclosure());
            $this->setCsvLineBreak($prefs->getCsvLineBreak());
            $this->setCsvDateFormat($prefs->getCsvDateFormat());
        }
        $filesHandler = [];
        $entityExporters = [];
        $entitiesNameWithArray = [
            'Determination',
            'Multimedia',
            'Bibliography',
        ];
        foreach ($this->datas as $key => $record) {
            foreach ($record as $className => $datasPerClass) {
                /* @var $entityExporter \AppBundle\Business\Exporter\Entity\AbstractEntityExporter */
                if (!isset($entityExporters[$className])) {
                    $entityExporters[$className] = $this->getEntityExporter($className);
                }
                $writeHeaders = false;

                // Creation des fichiers
                if (!isset($filesHandler[$className])) {
                    $this->createFile($className);
                    $filesHandler[$className] = fopen($this->files[$className]->getPathname(), 'w');
                    $writeHeaders = true;
                }

                // Ecrit les entêtes en première ligne de csv
                if ($writeHeaders) {
                    $this->fieldsName[$className] = $entityExporters[$className]->getKeysEntity();
                    $this->writeToFile($filesHandler[$className], $this->fieldsName[$className]);
                    $writeHeaders = false;
                }

                if (in_array($className, $entitiesNameWithArray)) {
                    // Traitement des extensions qui peuvent avoir plusieurs enregistrements pour un specimen
                    foreach ($datasPerClass as $arrayRecord) {
                        $filteredDatas = $this->filterDatas($arrayRecord, $entityExporters[$className], $className);
                        $this->writeToFile($filesHandler[$className], $filteredDatas);
                    }
                } else {
                    // traitement des extensions n'ayant qu'un enregistrement par specimen
                    $filteredDatas = $this->filterDatas($datasPerClass, $entityExporters[$className], $className);
                    $this->writeToFile($filesHandler[$className], $filteredDatas);
                }
            }
        }
        foreach ($filesHandler as $className => $fileHandler) {
            fclose($fileHandler);
        }
        if (isset($options['dwc']) && $options['dwc'] == true) {
            return $this->getFiles();
        } else {
            return $this->createZipFile();
        }
    }

    /**
     * @param string $zipFilename
     * @return string
     */
    private function createZipFile($zipFilename = 'csv.zip') {
        $fileExport = new \Symfony\Component\Filesystem\Filesystem();
        $zipFilePath = $this->getExportDirPath().'/'.$zipFilename;
        $arrayFilesName = [];
        foreach ($this->getFiles() as $csvFile) {
            $arrayFilesName[] = $csvFile->getPathName();
        }
        $zipCommand = sprintf('zip -j %s %s', $zipFilePath, implode(' ', $arrayFilesName));
        exec($zipCommand);
        $fileExport->chmod($zipFilePath, 0777);
        
        return $zipFilePath;
    }


    /**
     * Ecrit une ligne dans le fichier et ajoute un retour à la ligne
     * @param resource $fileHandler
     * @param array $datas
     */
    private function writeToFile($fileHandler, $datas)
    {
        $line = $this->arrayToCsv($datas, $this->getCsvDelimiter(), $this->getCsvEnclosure(), $this->getCsvLineBreak());
        fwrite($fileHandler, $line);
        fwrite($fileHandler, $this->getCsvLineBreak());
    }

    /**
     * Crée un fichier csv
     * @param string $className
     * @param string $extension
     */
    private function createFile($className, $extension = 'csv')
    {
        $fileExport = new \Symfony\Component\Filesystem\Filesystem();
        $fileName = $this->exportPath.'/'.strtolower($className).'.'.$extension;
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
     * @param string $className
     * @return array
     */
    public function filterDatas($datas, $entityExporter, $className)
    {
        $filteredDatas = [];
        if (count($datas)>0) {
            if (!isset($this->acceptedFieldsName[$className])) {
                foreach ($this->fieldsName[$className] as $fieldName) {
                    if ($entityExporter->exportToCsv($fieldName)) {
                        $this->acceptedFieldsName[$className][] = $fieldName;
                    }
                }
            }

            if (count($this->acceptedFieldsName[$className])>0) {
                foreach ($this->acceptedFieldsName[$className] as $acceptedFieldName) {
                    if (isset($datas[$acceptedFieldName])) {
                        $filteredDatas[$acceptedFieldName] = $datas[$acceptedFieldName];
                    }
                    else {
                        $filteredDatas[$acceptedFieldName] = null;
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
            if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc})/", $field)) {
                $output[] = $enclosure.str_replace(
                        $enclosure, $enclosure.$enclosure, 
                        $this->convertField($field, $this->getCsvDateFormat())
                        ).$enclosure;
            } else {
                $output[] = $field;
            }
        }

        return implode($delimiter, $output);
    }

    public function getCsvDelimiter()
    {
        return $this->csvDelimiter;
    }

    public function getCsvEnclosure()
    {
        return $this->csvEnclosure;
    }

    public function getCsvLineBreak()
    {
        return $this->csvLineBreak;
    }

    public function getCsvDateFormat()
    {
        return $this->csvDateFormat;
    }

    public function getCsvIgnoreHeaderLines()
    {
        return $this->csvIgnoreHeaderLines;
    }

    /**
     * @param string $csvDelimiter
     */
    public function setCsvDelimiter($csvDelimiter)
    {
        $this->csvDelimiter = stripcslashes($csvDelimiter);
    }

    /**
     * @param string $csvEnclosure
     */
    public function setCsvEnclosure($csvEnclosure)
    {
        $this->csvEnclosure = stripcslashes($csvEnclosure);
    }

    /**
     * @param string $csvLineBreak
     */
    public function setCsvLineBreak($csvLineBreak)
    {
        $this->csvLineBreak = stripcslashes($csvLineBreak);
    }

    /**
     * @param string $csvDateFormat
     */
    public function setCsvDateFormat($csvDateFormat)
    {
        $this->csvDateFormat = stripcslashes($csvDateFormat);
    }

    public function setCsvIgnoreHeaderLines($csvIgnoreHeaderLines)
    {
        $this->csvIgnoreHeaderLines = $csvIgnoreHeaderLines;
    }

}
