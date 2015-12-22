<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of AbstractEntityExporter
 *
 * @author tpateffoz
 */
abstract class AbstractEntityExporter
{
    public function exportToCsv($fieldName) {
        return $this->getExportProperties($fieldName) == '' ? false : true ;
    }
    public function getXmlTerm($fieldName) {
        return $this->getExportProperties($fieldName) ;
    }
    abstract static protected function getExportProperties($fieldName);
    abstract public function getNameSpace() ;
    abstract public function getIdFieldName() ;
    abstract public function getCoreIdFieldName() ;
}
