<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of BibliographyExporter
 *
 * @author tpateffoz
 */
class BibliographyExporter extends AbstractEntityExporter
{

    protected function getExportProperties($fieldName)
    {
        $term = '';
        switch ($fieldName) {
            case 'referenceid':
                $term = 'http://purl.org/dc/terms/identifier';
                break;
            case 'bibliographiccitation':
                $term = 'http://purl.org/dc/terms/bibliographicCitation';
                break;
            case 'creator':
                $term = 'http://purl.org/dc/terms/creator';
                break;
            case 'date_publication':
                $term = 'http://purl.org/dc/terms/date';
                break;
            case 'description':
                $term = 'http://purl.org/dc/terms/description';
                break;
            case 'identifier':
                $term = 'http://purl.org/dc/terms/identifier';
                break;
            case 'language':
                $term = 'http://purl.org/dc/terms/language';
                break;
            case 'rights':
                $term = 'http://purl.org/dc/terms/rights';
                break;
            case 'source':
                $term = 'http://purl.org/dc/terms/source';
                break;
            case 'subject':
                $term = 'http://purl.org/dc/terms/subject';
                break;
            case 'taxonremarks':
                $term = 'http://rs.tdwg.org/dwc/terms/taxonRemarks';
                break;
            case 'title':
                $term = 'http://purl.org/dc/terms/title';
                break;
            case 'type':
                $term = 'http://purl.org/dc/terms/type';
                break;
            case 'occurrenceid':
                $term = 'http://rs.gbif.org/terms/1.0/gbifID';
                break;
        }
        return $term;
    }

    public function getNameSpace()
    {
        return 'http://rs.gbif.org/terms/1.0';
    }

    public function getIdFieldName()
    {
        return 'referenceid';
    }

}
