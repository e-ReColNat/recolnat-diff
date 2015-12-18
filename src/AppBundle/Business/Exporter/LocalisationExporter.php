<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of LocalisationExporter
 *
 * @author tpateffoz
 */
class LocalisationExporter extends AbstractEntityExporter
{

    protected function getExportProperties($fieldName)
    {
        $term = '';
        switch ($fieldName) {
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
        return 'http://purl.org/dc/terms/Location';
    }

    public function getIdFieldName()
    {
        return 'locationid';
    }

}
