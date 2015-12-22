<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of StratigraphyExporter
 *
 * @author tpateffoz
 */
class StratigraphyExporter extends AbstractEntityExporter
{

    static protected function getExportProperties($fieldName)
    {
        $term = '';
        switch ($fieldName) {
            case 'occurrenceid':
                $term = 'http://rs.gbif.org/terms/1.0/gbifID';
                break;
            case 'geologicalcontextid':
                $term = 'http://rs.tdwg.org/dwc/terms/geologicalContextID';
                break;
            case 'bed':
                $term = 'http://rs.tdwg.org/dwc/terms/bed';
                break;
            case 'earliestageorloweststage':
                $term = 'http://rs.tdwg.org/dwc/terms/earliestAgeOrLowestStage';
                break;
            case 'earliesteonorlowesteonothem':
                $term = 'http://rs.tdwg.org/dwc/terms/earliestEonOrLowestEonothem';
                break;
            case 'earliestepochorlowestseries':
                $term = 'http://rs.tdwg.org/dwc/terms/earliestEpochOrLowestSeries';
                break;
            case 'earliesteraorlowesterathem':
                $term = 'http://rs.tdwg.org/dwc/terms/earliestEraOrLowestErathem';
                break;
            case 'earliestperiodorlowestsystem':
                $term = 'http://rs.tdwg.org/dwc/terms/earliestPeriodOrLowestSystem';
                break;
            case 'formation':
                $term = 'http://rs.tdwg.org/dwc/terms/formation';
                break;
            case 'group_':
                $term = 'http://rs.tdwg.org/dwc/terms/group';
                break;
            case 'highestbiostratigraphiczone':
                $term = 'http://rs.tdwg.org/dwc/terms/highestBiostratigraphicZone';
                break;
            case 'latestageorhigheststage':
                $term = 'http://rs.tdwg.org/dwc/terms/latestAgeOrHighestStage';
                break;
            case 'latesteonorhighesteonothem':
                $term = 'http://rs.tdwg.org/dwc/terms/latestEonOrHighestEonothem';
                break;
            case 'latestepochorhighestseries':
                $term = 'http://rs.tdwg.org/dwc/terms/latestEpochOrHighestSeries';
                break;
            case 'latesteraorhighesterathem':
                $term = 'http://rs.tdwg.org/dwc/terms/latestEraOrHighestErathem';
                break;
            case 'latestperiodorhighestsystem':
                $term = 'http://rs.tdwg.org/dwc/terms/latestPeriodOrHighestSystem';
                break;
            case 'lowestbiostratigraphiczone':
                $term = 'http://rs.tdwg.org/dwc/terms/lowestBiostratigraphicZone';
                break;
            case 'member':
                $term = 'http://rs.tdwg.org/dwc/terms/member';
                break;
        }
        return $term;
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
