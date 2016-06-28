<?php
namespace AppBundle\Manager;

class DiffTaxon extends AbstractDiff
{
    public $excludeFieldsName = ['created', 'modified', 'sourcefileid', 'dwcataxonid'];

    public static function getIdSetter()
    {
        return 'getTaxonid';
    }

    public static function getIdField()
    {
        return 'taxonid';
    }
}
