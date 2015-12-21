<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of MultimediaExporter
 *
 * @author tpateffoz
 */
class MultimediaExporter extends AbstractEntityExporter
{

    protected function getExportProperties($fieldName)
    {
        $term = '';
        switch ($fieldName) {
            case 'occurrenceid':
                $term = 'http://rs.gbif.org/terms/1.0/gbifID';
                break;
            case 'multimediaid':
                $term = 'http://purl.org/dc/terms/identifier';
                break;
            case 'audience':
                $term = 'http://purl.org/dc/terms/audience';
                break;
            case 'contributor':
                $term = 'http://purl.org/dc/terms/contributor';
                break;
            case 'created':
                $term = 'http://purl.org/dc/terms/created';
                break;
            case 'creator':
                $term = 'http://purl.org/dc/terms/creator';
                break;
            case 'description':
                $term = 'http://purl.org/dc/terms/description';
                break;
            case 'discriminator':
                $term = '';
                break;
            case 'format':
                $term = 'http://purl.org/dc/terms/format';
                break;
            case 'identifier':
                $term = 'http://purl.org/dc/terms/identifier';
                break;
            case 'license':
                $term = 'http://purl.org/dc/terms/license';
                break;
            case 'publisher':
                $term = 'http://purl.org/dc/terms/publisher';
                break;
            case 'references':
                $term = 'http://purl.org/dc/terms/references';
                break;
            case 'rights':
                $term = '';
                break;
            case 'rightsholder':
                $term = 'http://purl.org/dc/terms/rightsHolder';
                break;
            case 'source':
                $term = 'http://purl.org/dc/terms/source';
                break;
            case 'title':
                $term = 'http://purl.org/dc/terms/title';
                break;
            case 'type':
                $term = 'http://purl.org/dc/terms/type';
                break;
        }
        return $term;
    }

    public function getNameSpace()
    {
        return 'http://rs.tdwg.org/dwc/terms/Multimedia';
    }

    public function getIdFieldName()
    {
        return 'multimediaid';
    }
    
    public function getCoreIdFieldName()
    {
        return 'occurrenceid';
    }
}
