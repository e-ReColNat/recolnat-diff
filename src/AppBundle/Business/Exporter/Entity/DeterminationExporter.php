<?php

namespace AppBundle\Business\Exporter\Entity;

/**
 * Description of DeterminationExporter
 *
 * @author tpateffoz
 */
class DeterminationExporter extends AbstractEntityExporter
{

    public function getExportProperty($fieldName) {
        if (array_key_exists($fieldName, $this->arrayExportTerm)) {
            return $this->arrayExportTerm[$fieldName] ;
        }
        else {
            $taxonExporter = new TaxonExporter() ;
            return $taxonExporter->getXmlTerm($fieldName);
        }
        return null ;
    }
    public function setExportTerm() 
    {
        $this->arrayExportTerm = [
            'occurrenceid' => 'http://rs.gbif.org/terms/1.0/gbifID',
            'identificationid' => 'http://rs.tdwg.org/dwc/terms/identificationID',
            'created' => 'http://purl.org/dc/terms/created',
            'dateidentified' => 'http://rs.tdwg.org/dwc/terms/dateIdentified',
            'identificationqualifier' => 'http://rs.tdwg.org/dwc/terms/identificationQualifier',
            'identificationreferences' => 'http://rs.tdwg.org/dwc/terms/identificationReferences',
            'identificationremarks' => 'http://rs.tdwg.org/dwc/terms/identificationRemarks',
            'identificationverifstatus' => 'http://rs.tdwg.org/dwc/terms/typeStatus',
            'identifiedby' => 'http://rs.tdwg.org/dwc/terms/identifiedBy',
            'modified' => 'http://purl.org/dc/terms/modified',
            'typestatus' => 'http://rs.tdwg.org/dwc/terms/typeStatus',
            'taxonid' => 'http://rs.tdwg.org/dwc/terms/taxonID',
        ];
    }    
    public function getNameSpace()
    {
        return 'http://rs.tdwg.org/dwc/terms/Identification';
    }

    public function getIdFieldName()
    {
        return 'identificationid';
    }
    
    public function getCoreIdFieldName()
    {
        return 'occurrenceid';
    }
}
