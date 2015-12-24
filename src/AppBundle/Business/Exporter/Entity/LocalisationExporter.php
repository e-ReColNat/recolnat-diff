<?php

namespace AppBundle\Business\Exporter\Entity;

/**
 * Description of LocalisationExporter
 *
 * @author tpateffoz
 */
class LocalisationExporter extends AbstractEntityExporter
{

    public function setExportTerm()
    {
        $this->arrayExportTerm = [
            'occurrenceid' => 'http://rs.gbif.org/terms/1.0/gbifID',
            'locationid' => 'http://rs.tdwg.org/dwc/terms/locationID',
            'averagealtituderounded' => '',
            'continent' => 'http://rs.tdwg.org/dwc/terms/continent',
            'country' => 'http://rs.tdwg.org/dwc/terms/country',
            'countrycode' => 'http://rs.tdwg.org/dwc/terms/countryCode',
            'county' => 'http://rs.tdwg.org/dwc/terms/county',
            'decimallatitude' => 'http://rs.tdwg.org/dwc/terms/decimalLatitude',
            'decimallongitude' => 'http://rs.tdwg.org/dwc/terms/decimalLongitude',
            'geodeticdatum' => 'http://rs.tdwg.org/dwc/terms/geodeticDatum',
            'georeferencesources' => 'http://rs.tdwg.org/dwc/terms/georeferenceSources',
            'hascoordinates' => '',
            'locality' => 'http://rs.tdwg.org/dwc/terms/locality',
            'locationremarks' => 'http://rs.tdwg.org/dwc/terms/locationRemarks',
            'maximumdepthinmeters' => 'http://rs.tdwg.org/dwc/terms/maximumDepthInMeters',
            'maximumelevationinmeters' => 'http://rs.tdwg.org/dwc/terms/maximumElevationInMeters',
            'minimumdepthinmeters' => 'http://rs.tdwg.org/dwc/terms/minimumDepthInMeters',
            'minimumelevationinmeters' => 'http://rs.tdwg.org/dwc/terms/minimumElevationInMeters',
            'municipality' => 'http://rs.tdwg.org/dwc/terms/municipality',
            'sourcefileid' => '',
            'stateprovince' => 'http://rs.tdwg.org/dwc/terms/stateProvince',
            'verbatimcountry' => '',
            'verbatimelevation' => 'http://rs.tdwg.org/dwc/terms/verbatimElevation',
            'verbatimlocality' => 'http://rs.tdwg.org/dwc/terms/verbatimLocality',
        ];
    }

    public function getNameSpace()
    {
        return 'http://purl.org/dc/terms/Location';
    }

    public function getIdFieldName()
    {
        return 'locationid';
    }
    public function getCoreIdFieldName()
    {
        return null;
    }
}
