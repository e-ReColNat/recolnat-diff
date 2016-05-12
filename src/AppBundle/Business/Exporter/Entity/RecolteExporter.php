<?php

namespace AppBundle\Business\Exporter\Entity;

/**
 * Description of RecolteExporter
 *
 * @author tpateffoz
 */
class RecolteExporter extends AbstractEntityExporter
{
    public function getExportProperties()
    {
        $localisationExporter = new LocalisationExporter();
        return array_merge($this->arrayExportTerm, $localisationExporter->getExportProperties());
    }

    public function setExportTerm()
    {
        $this->arrayExportTerm = [
            //'occurrenceid' => 'http://rs.gbif.org/terms/1.0/gbifID',
            //'locationid' => 'http://rs.tdwg.org/dwc/terms/locationID',
            'eventid' => 'http://rs.tdwg.org/dwc/terms/eventID',
            'eventdate' => 'http://rs.tdwg.org/dwc/terms/eventDate',
            'eventremarks' => 'http://rs.tdwg.org/dwc/terms/eventRemarks',
            'fieldnotes' => 'http://rs.tdwg.org/dwc/terms/fieldNotes',
            'fieldnumber' => 'http://rs.tdwg.org/dwc/terms/fieldNumber',
            'habitat' => 'http://rs.tdwg.org/dwc/terms/habitat',
            'verbatimeventdate' => 'http://rs.tdwg.org/dwc/terms/verbatimEventDate',
        ];
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
