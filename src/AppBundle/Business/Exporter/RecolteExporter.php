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
            $term = $this->getLocalisationExportProperties($fieldName);
        }
        return $term;
    }
    protected function getLocalisationExportProperties($fieldName)
    {
        $term = '';
        switch ($fieldName) {
            case 'occurrenceid':
                $term = 'http://rs.gbif.org/terms/1.0/gbifID';
                break;
            case 'locationid':
                $term = 'http://rs.tdwg.org/dwc/terms/locationID';
                break;
            case 'continent':
                $term = 'http://rs.tdwg.org/dwc/terms/continent';
                break;
            case 'country':
                $term = 'http://rs.tdwg.org/dwc/terms/country';
                break;
            case 'countrycode':
                $term = 'http://rs.tdwg.org/dwc/terms/countryCode';
                break;
            case 'county':
                $term = 'http://rs.tdwg.org/dwc/terms/county';
                break;
            case 'decimallatitude':
                $term = 'http://rs.tdwg.org/dwc/terms/decimalLatitude';
                break;
            case 'decimallongitude':
                $term = 'http://rs.tdwg.org/dwc/terms/decimalLongitude';
                break;
            case 'geodeticdatum':
                $term = 'http://rs.tdwg.org/dwc/terms/geodeticDatum';
                break;
            case 'georeferencesources':
                $term = 'http://rs.tdwg.org/dwc/terms/georeferenceSources';
                break;
            case 'locality':
                $term = 'http://rs.tdwg.org/dwc/terms/locality';
                break;
            case 'locationremarks':
                $term = 'http://rs.tdwg.org/dwc/terms/locationRemarks';
                break;
            case 'maximumdepthinmeters':
                $term = 'http://rs.tdwg.org/dwc/terms/maximumDepthInMeters';
                break;
            case 'maximumelevationinmeters':
                $term = 'http://rs.tdwg.org/dwc/terms/maximumElevationInMeters';
                break;
            case 'minimumdepthinmeters':
                $term = 'http://rs.tdwg.org/dwc/terms/minimumDepthInMeters';
                break;
            case 'minimumelevationinmeters':
                $term = 'http://rs.tdwg.org/dwc/terms/minimumElevationInMeters';
                break;
            case 'municipality':
                $term = 'http://rs.tdwg.org/dwc/terms/municipality';
                break;
            case 'stateprovince':
                $term = 'http://rs.tdwg.org/dwc/terms/stateProvince';
                break;
            case 'verbatimcountry':
                $term = '';
                break;
            case 'verbatimelevation':
                $term = 'http://rs.tdwg.org/dwc/terms/verbatimElevation';
                break;
            case 'verbatimlocality':
                $term = 'http://rs.tdwg.org/dwc/terms/verbatimLocality';
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
    
    public function getCoreIdFieldName()
    {
        return 'occurrenceid';
    }
}
