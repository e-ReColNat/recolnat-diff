<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of RecolteExporter
 *
 * @author tpateffoz
 */
class RecolteExporter extends AbstractEntityExporter
{

    protected function getExportProperties($fieldName)
    {
        $term = '';
        switch ($fieldName) {
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
            case 'recordedby':
                $term = 'http://rs.tdwg.org/dwc/terms/recordedBy';
                break;
            case 'verbatimeventdate':
                $term = 'http://rs.tdwg.org/dwc/terms/verbatimEventDate';
                break;
            case 'locationid':
                $term = 'http://rs.tdwg.org/dwc/terms/locationID';
                break;
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

}
