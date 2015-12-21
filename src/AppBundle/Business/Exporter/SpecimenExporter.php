<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of Specimen
 *
 * @author tpateffoz
 */
class SpecimenExporter extends AbstractEntityExporter
{

    protected function getExportProperties($fieldName)
    {
        $term = '';
        switch ($fieldName) {
            case 'occurrenceid':
                $term = 'http://rs.gbif.org/terms/1.0/gbifID';
                break;
            case 'accessrights':
                $term = 'http://purl.org/dc/terms/accessRights';
                break;
            case 'associatedmedia':
                $term = 'http://rs.tdwg.org/dwc/terms/associatedMedia';
                break;
            case 'associatedreferences':
                $term = 'http://rs.tdwg.org/dwc/terms/associatedReferences';
                break;
            case 'associatedtaxa':
                $term = 'http://rs.tdwg.org/dwc/terms/associatedTaxa';
                break;
            case 'basisofrecord':
                $term = 'http://rs.tdwg.org/dwc/terms/basisOfRecord';
                break;
            case 'bibliographiccitation':
                $term = 'http://purl.org/dc/terms/bibliographicCitation';
                break;
            case 'catalognumber':
                $term = 'http://rs.tdwg.org/dwc/terms/catalogNumber';
                break;
            case 'collectioncode':
                $term = 'http://rs.tdwg.org/dwc/terms/collectionCode';
                break;
            case 'created':
                $term = 'http://purl.org/dc/terms/created';
                break;
            case 'disposition':
                $term = 'http://rs.tdwg.org/dwc/terms/disposition';
                break;
            case 'lifestage':
                $term = 'http://rs.tdwg.org/dwc/terms/lifeStage';
                break;
            case 'modified':
                $term = 'http://purl.org/dc/terms/modified';
                break;
            case 'occurrenceremarks':
                $term = 'http://rs.tdwg.org/dwc/terms/occurrenceRemarks';
                break;
            case 'ownerinstitutioncode':
                $term = 'http://rs.tdwg.org/dwc/terms/ownerInstitutionCode';
                break;
            case 'recordnumber':
                $term = 'http://rs.tdwg.org/dwc/terms/institutionCode';
                break;
            case 'rights':
                $term = 'http://purl.org/dc/terms/rights';
                break;
            case 'rightsholder':
                $term = 'http://purl.org/dc/terms/rightsHolder';
                break;
            case 'sex':
                $term = 'http://rs.tdwg.org/dwc/terms/sex';
                break;
            case 'geologicalcontextid':
                $term = 'http://rs.tdwg.org/dwc/terms/geologicalContextID';
                break;
            case 'eventid':
                $term = 'http://rs.tdwg.org/dwc/terms/eventID';
                break;
        }
        return $term;
    }

    public function getNameSpace()
    {
        return 'http://rs.tdwg.org/dwc/terms/Occurrence';
    }

    public function getIdFieldName()
    {
        return 'occurrenceid';
    }
    
    public function getCoreIdFieldName()
    {
        return 'occurrenceid';
    }
}
