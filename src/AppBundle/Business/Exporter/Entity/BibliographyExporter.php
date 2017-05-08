<?php

namespace AppBundle\Business\Exporter\Entity;

/**
 * Description of BibliographyExporter
 *
 * @author tpateffoz
 */
class BibliographyExporter extends AbstractEntityExporter
{
    
    public function setExportTerm()
    {
        $this->arrayExportTerm = [
            'occurrenceid' => 'http://rs.gbif.org/terms/1.0/gbifID',
            'referenceid' => 'http://purl.org/dc/terms/identifier',
            'bibliographiccitation' => 'http://purl.org/dc/terms/bibliographicCitation',
            'creator' => 'http://purl.org/dc/terms/creator',
            'date_publication' => 'http://purl.org/dc/terms/date',
            'description' => 'http://purl.org/dc/terms/description',
            'identifier' => 'http://purl.org/dc/terms/identifier',
            'language' => 'http://purl.org/dc/terms/language',
            'rights' => 'http://purl.org/dc/terms/rights',
            'source' => 'http://purl.org/dc/terms/source',
            'subject' => 'http://purl.org/dc/terms/subject',
            'taxonremarks' => 'http://rs.tdwg.org/dwc/terms/taxonRemarks',
            'title' => 'http://purl.org/dc/terms/title',
            'type' => 'http://purl.org/dc/terms/type',
        ];
    }

    public function getNameSpace()
    {
        return 'http://rs.gbif.org/terms/1.0/Reference';
    }

    public function getIdFieldName()
    {
        return 'discriminationId';
    }
    public function getCoreIdFieldName()
    {
        return 'occurrenceid';
    }

}
