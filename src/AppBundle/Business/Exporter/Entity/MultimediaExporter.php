<?php

namespace AppBundle\Business\Exporter\Entity;

/**
 * Description of MultimediaExporter
 *
 * @author tpateffoz
 */
class MultimediaExporter extends AbstractEntityExporter
{
    public function setExportTerm()
    {
        $this->arrayExportTerm = [
            'occurrenceid' => 'http://rs.gbif.org/terms/1.0/gbifID',
            'multimediaid' => 'http://purl.org/dc/terms/identifier',
            'audience' => 'http://purl.org/dc/terms/audience',
            'contributor' => 'http://purl.org/dc/terms/contributor',
            'created' => 'http://purl.org/dc/terms/created',
            'creator' => 'http://purl.org/dc/terms/creator',
            'description' => 'http://purl.org/dc/terms/description',
            'discriminator' => '',
            'format' => 'http://purl.org/dc/terms/format',
            'identifier' => 'http://purl.org/dc/terms/identifier',
            'license' => 'http://purl.org/dc/terms/license',
            'modified' => '',
            'publisher' => 'http://purl.org/dc/terms/publisher',
            'references' => 'http://purl.org/dc/terms/references',
            'rights' => '',
            'rightsholder' => 'http://purl.org/dc/terms/rightsHolder',
            'source' => 'http://purl.org/dc/terms/source',
            'title' => 'http://purl.org/dc/terms/title',
            'type' => 'http://purl.org/dc/terms/type',
        ];
    }

    public function getNameSpace()
    {
        return 'http://rs.gbif.org/terms/1.0/Multimedia';
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
