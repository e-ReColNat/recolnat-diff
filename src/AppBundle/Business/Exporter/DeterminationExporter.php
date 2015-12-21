<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of DeterminationExporter
 *
 * @author tpateffoz
 */
class DeterminationExporter extends AbstractEntityExporter
{

    protected function getExportProperties($fieldName)
    {
        $term = '';
        switch ($fieldName) {
            case 'occurrenceid':
                $term = 'http://rs.gbif.org/terms/1.0/gbifID';
                break;
            case 'identificationid':
                $term = 'http://rs.tdwg.org/dwc/terms/identificationID';
                break;
            case 'created':
                $term = 'http://purl.org/dc/terms/created';
                break;
            case 'dateidentified':
                $term = 'http://rs.tdwg.org/dwc/terms/dateIdentified';
                break;
            case 'identificationqualifier':
                $term = 'http://rs.tdwg.org/dwc/terms/identificationQualifier';
                break;
            case 'identificationreferences':
                $term = 'http://rs.tdwg.org/dwc/terms/identificationReferences';
                break;
            case 'identificationremarks':
                $term = 'http://rs.tdwg.org/dwc/terms/identificationRemarks';
                break;
            case 'identificationverifstatus':
                $term = 'http://rs.tdwg.org/dwc/terms/typeStatus';
                break;
            case 'identifiedby':
                $term = 'http://rs.tdwg.org/dwc/terms/identifiedBy';
                break;
            case 'modified':
                $term = 'http://purl.org/dc/terms/modified';
                break;
            case 'typestatus':
                $term = 'http://rs.tdwg.org/dwc/terms/typeStatus';
                break;
            case 'taxonid':
                $term = 'http://rs.tdwg.org/dwc/terms/taxonID';
                break;
        }
        return $term;
    }

    public function getNameSpace()
    {
        return 'http://rs.tdwg.org/dwc/terms/Determination';
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
