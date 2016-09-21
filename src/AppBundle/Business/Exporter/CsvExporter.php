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
    protected $csvIgnoreHeaderLines;

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
        if (isset($options['dwc']) && $options['dwc'] === true) {
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
        /* @var \AppBundle\Business\Exporter\Entity\AbstractEntityExporter $entityExporters [] */
        $entityExporters = [];
        $entitiesNameWithArray = [
            'Determination',
            'Multimedia',
            'Bibliography',
        ];

        foreach ($this->datas as $key => $record) {
            foreach ($record as $className => $datasPerClass) {

                if (!isset($entityExporters[$className])) {
                    $entityExporters[$className] = $this->getEntityExporter($className);
                }

                // Creation des fichiers
                if (!isset($filesHandler[$className])) {
                    $this->createFile($className);
                    $filesHandler[$className] = fopen($this->files[$className]->getPathname(), 'w');

                    // Ecrit les entêtes en première ligne de csv
                    $this->fieldsName[$className] = $entityExporters[$className]->getKeysEntity();
                    $this->writeToFile($filesHandler[$className], $this->fieldsName[$className]);
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
        if (isset($options['dwc']) && $options['dwc'] === true) {
            return $this->getFiles();
        } else {
            return $this->createZipFile();
        }
    }

    /**
     * @param string $zipFilename
     * @return string
     */
    private function createZipFile($zipFilename = 'csv.zip')
    {
        $zipFilePath = $this->getExportDirPath().'/'.$zipFilename;
        $arrayFilesName = [];
        foreach ($this->getFiles() as $className => $csvFile) {
            /** @var \SplFileObject $csvFile */
            $arrayFilesName[] = $csvFile->getPathName();
            // Closing files
            $this->files[$className] = null;
        }
        $zipCommand = sprintf('zip -j %s %s', $zipFilePath, implode(' ', $arrayFilesName));
        exec($zipCommand);

        $this->removeFiles($arrayFilesName);

        return $zipFilePath;
    }

    /**
     * Ecrit une ligne dans le fichier et ajoute un retour à la ligne
     * @param resource $fileHandler
     * @param array    $datas
     */
    private function writeToFile($fileHandler, $datas)
    {
        $line = $this->arrayToCsv($datas, $this->getCsvDelimiter(), $this->getCsvEnclosure(), true);
        if (strlen($line)) {
            fwrite($fileHandler, $line);
            fwrite($fileHandler, $this->getCsvLineBreak());
        }
    }

    /**
     * Crée un fichier csv
     * @param string $className
     * @param string $extension
     */
    private function createFile($className, $extension = 'csv')
    {
        $fileName = $this->exportPath.'/'.strtolower($className).'.'.$extension;
        if (!is_file($fileName)) {
            exec('touch '.$fileName);
        }

        $this->files[$className] = new \SplFileObject($fileName);
    }

    /**
     * @return \ArrayObject
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     *  renvoie les données filtrées en fonction des champs acceptés
     * @param array                         $datas
     * @param Entity\AbstractEntityExporter $entityExporter
     * @param string                        $className
     * @return array
     */
    public function filterDatas($datas, $entityExporter, $className)
    {
        $filteredDatas = [];
        if (count($datas) > 0) {
            if (!isset($this->acceptedFieldsName[$className])) {
                foreach ($this->fieldsName[$className] as $fieldName) {
                    if ($entityExporter->shouldExportToCsv($fieldName)) {
                        $this->acceptedFieldsName[$className][] = $fieldName;
                    }
                }
            }

            if (count($this->acceptedFieldsName[$className]) > 0) {
                foreach ($this->acceptedFieldsName[$className] as $acceptedFieldName) {
                    if (isset($datas[$acceptedFieldName])) {
                        $filteredDatas[$acceptedFieldName] = $datas[$acceptedFieldName];
                    } else {
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
    /**
     * @param array  $fields
     * @param string $delimiter
     * @param string $enclosure
     * @param bool   $encloseAll
     * @param bool   $nullToMysqlNull
     * @return string
     */
    private function arrayToCsv(
        array &$fields,
        $delimiter = ';',
        $enclosure = '"',
        $encloseAll = false,
        $nullToMysqlNull = false
    ) {
        $escDelimiter = preg_quote($delimiter, '/');
        $escEnclosure = preg_quote($enclosure, '/');

        $output = array();
        foreach ($fields as $field) {
            if ($field === null && $nullToMysqlNull) {
                $output[] = 'NULL';
                continue;
            }

            // Enclose fields containing $delimiter, $enclosure or whitespace
            if ($encloseAll || preg_match("/(?:${escDelimiter}|${escEnclosure})/", $field)) {
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

    /**
     * @return string
     */
    public function getCsvDelimiter()
    {
        return $this->csvDelimiter;
    }

    /**
     * @return string
     */
    public function getCsvEnclosure()
    {
        return $this->csvEnclosure;
    }

    /**
     * @return string
     */
    public function getCsvLineBreak()
    {
        return $this->csvLineBreak;
    }

    /**
     * @return string
     */
    public function getCsvDateFormat()
    {
        return $this->csvDateFormat;
    }

    /**
     * @return boolean
     */
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

    /**
     * @param boolean $csvIgnoreHeaderLines
     */
    public function setCsvIgnoreHeaderLines($csvIgnoreHeaderLines)
    {
        $this->csvIgnoreHeaderLines = $csvIgnoreHeaderLines;
    }

}
