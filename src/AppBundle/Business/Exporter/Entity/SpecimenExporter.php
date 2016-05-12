<?php

namespace AppBundle\Business\Exporter\Entity;

/**
 * Description of Specimen
 *
 * @author tpateffoz
 */
class SpecimenExporter extends AbstractEntityExporter
{

        public function getExportProperties()
    {
        $stratigraphyExporter = new StratigraphyExporter();
        $recolteExporter = new RecolteExporter();
        $localisationExporter = new LocalisationExporter();
        return array_merge(
            $this->arrayExportTerm,
            $stratigraphyExporter->getExportProperties(),
            $recolteExporter->getExportProperties(),
            $localisationExporter->getExportProperties()
        );
    }
    
    public function setExportTerm() 
    {
        $this->arrayExportTerm = [
            'occurrenceid' => 'http://rs.gbif.org/terms/1.0/gbifID',
            'accessrights' => 'http://purl.org/dc/terms/accessRights',
            'associatedmedia' => 'http://rs.tdwg.org/dwc/terms/associatedMedia',
            'associatedreferences' => 'http://rs.tdwg.org/dwc/terms/associatedReferences',
            'associatedtaxa' => 'http://rs.tdwg.org/dwc/terms/associatedTaxa',
            'basisofrecord' => 'http://rs.tdwg.org/dwc/terms/basisOfRecord',
            'bibliographiccitation' => 'http://purl.org/dc/terms/bibliographicCitation',
            'catalognumber' => 'http://rs.tdwg.org/dwc/terms/catalogNumber',
            'collectioncode' => 'http://rs.tdwg.org/dwc/terms/collectionCode',
            'created' => 'http://purl.org/dc/terms/created',
            'disposition' => 'http://rs.tdwg.org/dwc/terms/disposition',
            'lifestage' => 'http://rs.tdwg.org/dwc/terms/lifeStage',
            'modified' => 'http://purl.org/dc/terms/modified',
            'occurrenceremarks' => 'http://rs.tdwg.org/dwc/terms/occurrenceRemarks',
            'ownerinstitutioncode' => 'http://rs.tdwg.org/dwc/terms/ownerInstitutionCode',
            'recordnumber' => 'http://rs.tdwg.org/dwc/terms/recordNumber',
            'rights' => 'http://purl.org/dc/terms/rights',
            'rightsholder' => 'http://purl.org/dc/terms/rightsHolder',
            'sex' => 'http://rs.tdwg.org/dwc/terms/sex',
            'eventid' => 'http://rs.tdwg.org/dwc/terms/eventID',
        ];
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
