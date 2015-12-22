<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of RecolteExporter
 *
 * @author tpateffoz
 */
class RecolteExporter extends AbstractEntityExporter
{

    static protected function getExportProperties($fieldName)
    {
        $term = '';
        switch ($fieldName) {
            case 'occurrenceid':
                $term = 'http://rs.gbif.org/terms/1.0/gbifID';
                break;
            case 'locationid':
                $term = 'http://rs.tdwg.org/dwc/terms/locationID';
                break;
            case 'eventid':
                $term = 'http://rs.tdwg.org/dwc/terms/eventID';
                break;
            case 'eventdate':
                $term = 'http://rs.tdwg.org/dwc/terms/eventDate';
                break;
            case 'eventremarks':
                $term = 'http://rs.tdwg.org/dwc/terms/eventRemarks';
                break;
            case 'fieldnotes':
                $term = 'http://rs.tdwg.org/dwc/terms/fieldNotes';
                break;
            case 'fieldnumber':
                $term = 'http://rs.tdwg.org/dwc/terms/fieldNumber';
                break;
            case 'habitat':
                $term = 'http://rs.tdwg.org/dwc/terms/habitat';
                break;
            /*case 'recordedby':
                $term = 'http://rs.tdwg.org/dwc/terms/recordedBy';
                break;*/
            case 'verbatimeventdate':
                $term = 'http://rs.tdwg.org/dwc/terms/verbatimEventDate';
                break;
            case 'locationid':
                $term = 'http://rs.tdwg.org/dwc/terms/locationID';
                break;
        }
        if ($term  == '') {
            $term = LocalisationExporter::getExportProperties($fieldName);
        }
        return $term;
    }
    
    public function getNameSpace()
    {
        return 'http://rs.tdwg.org/dwc/terms/Event';
    }

    public function getIdFieldName()
    {
        return 'eventid';
    }
    
    public function getCoreIdFieldName()
    {
        return 'occurrenceid';
    }
}
