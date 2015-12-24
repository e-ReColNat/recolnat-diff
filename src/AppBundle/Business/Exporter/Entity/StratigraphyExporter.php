<?php

namespace AppBundle\Business\Exporter\Entity;

/**
 * Description of StratigraphyExporter
 *
 * @author tpateffoz
 */
class StratigraphyExporter extends AbstractEntityExporter
{

    public function setExportTerm() 
    {
        $this->arrayExportTerm = [
            'occurrenceid' => 'http://rs.gbif.org/terms/1.0/gbifID',
            'geologicalcontextid' => 'http://rs.tdwg.org/dwc/terms/geologicalContextID',
            'bed' => 'http://rs.tdwg.org/dwc/terms/bed',
            'earliestageorloweststage' => 'http://rs.tdwg.org/dwc/terms/earliestAgeOrLowestStage',
            'earliesteonorlowesteonothem' => 'http://rs.tdwg.org/dwc/terms/earliestEonOrLowestEonothem',
            'earliestepochorlowestseries' => 'http://rs.tdwg.org/dwc/terms/earliestEpochOrLowestSeries',
            'earliesteraorlowesterathem' => 'http://rs.tdwg.org/dwc/terms/earliestEraOrLowestErathem',
            'earliestperiodorlowestsystem' => 'http://rs.tdwg.org/dwc/terms/earliestPeriodOrLowestSystem',
            'formation' => 'http://rs.tdwg.org/dwc/terms/formation',
            'group' => 'http://rs.tdwg.org/dwc/terms/group',
            'highestbiostratigraphiczone' => 'http://rs.tdwg.org/dwc/terms/highestBiostratigraphicZone',
            'latestageorhigheststage' => 'http://rs.tdwg.org/dwc/terms/latestAgeOrHighestStage',
            'latesteonorhighesteonothem' => 'http://rs.tdwg.org/dwc/terms/latestEonOrHighestEonothem',
            'latestepochorhighestseries' => 'http://rs.tdwg.org/dwc/terms/latestEpochOrHighestSeries',
            'latesteraorhighesterathem' => 'http://rs.tdwg.org/dwc/terms/latestEraOrHighestErathem',
            'latestperiodorhighestsystem' => 'http://rs.tdwg.org/dwc/terms/latestPeriodOrHighestSystem',
            'lowestbiostratigraphiczone' => 'http://rs.tdwg.org/dwc/terms/lowestBiostratigraphicZone',
            'member' => 'http://rs.tdwg.org/dwc/terms/member',
        ];
    }
    public function getNameSpace()
    {
        return 'http://rs.tdwg.org/dwc/terms/GeologicalContext';
    }

    public function getIdFieldName()
    {
        return 'geologicalcontextid';
    }
    
    public function getCoreIdFieldName()
    {
        return 'occurrenceid';
    }
}
