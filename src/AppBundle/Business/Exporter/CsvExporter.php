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
        $filesHandler=[];
        foreach ($this->datas as $key => $record) {
            foreach ($record as $className => $datasPerClass) {
                $entityExporter = $this->getEntityExporter($className);
                $writeHeaders = false;
                if (!isset($filesHandler[$className])) {
                    $this->createFiles($className);
                    $filesHandler[$className] = fopen($this->files[$className]->getPathname(), 'w');
                    $writeHeaders = true;
                }
                
                
                if ($writeHeaders) {
                    $fieldsName =$entityExporter->getKeysEntity();
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

    public function generateForDwc() 
    {
        $filesHandler=[];
        $entitiesNameWithArray=[
            'Determination',
            'Taxon',
            'Multimedia',
            'Bibliography',
        ];
        foreach ($this->datas as $key => $record) {
            foreach ($record as $className => $datasPerClass) {
                $entityExporter = $this->getEntityExporter($className);
                $writeHeaders = false;
                if (!isset($filesHandler[$className])) {
                    $this->createFiles($className);
                    $filesHandler[$className] = fopen($this->files[$className]->getPathname(), 'w');
                    $writeHeaders = true;
                }
                
                
                if ($writeHeaders) {
                    $fieldsName =$entityExporter->getKeysEntity();
                    fputcsv($filesHandler[$className], $fieldsName, "\t");
                    $writeHeaders = false;
                }
                if (in_array($className, $entitiesNameWithArray)) {
                    foreach ($datasPerClass as $arrayRecord) {
                        $filteredDatas = $this->filterDatas($arrayRecord, $entityExporter);
                        fputcsv($filesHandler[$className], $filteredDatas, "\t");
                    }
                }
                else {
                    $filteredDatas = $this->filterDatas($datasPerClass, $entityExporter);
                    fputcsv($filesHandler[$className], $filteredDatas, "\t");
                }
            }
        }
        foreach ($filesHandler as $className => $fileHandler) {
            fclose($fileHandler);
        }
    }
    
    private function createFiles($className) {
        $fileExport = new \Symfony\Component\Filesystem\Filesystem();
        $fileName = $this->exportPath . '/' . strtolower($className) . '.csv';
        $fileExport->touch($fileName);
        $fileExport->chmod($fileName, 0777);
        $this->files[$className]=new \SplFileObject($fileName) ;
    }
    public function getFiles()
    {
        return $this->files;
    }

    public function filterDatas($datas, $entityExporter)
    {
        $filteredDatas = [];
        if (count($datas) > 0) {
            $acceptedFieldsName = [];
            $fieldsName =$entityExporter->getKeysEntity();
            foreach ($fieldsName as $fieldName) {
                if ($entityExporter->exportToCsv($fieldName)) {
                    $acceptedFieldsName[] = $fieldName;
                }
            }
            if (count($acceptedFieldsName) > 0) {
                foreach ($datas as $fieldName => $value) {
                    if (in_array($fieldName, $acceptedFieldsName)) {
                        $filteredDatas[$fieldName] = $datas[$fieldName] ;
                    }
                }
            }
        }
        return $filteredDatas;
    }

}
