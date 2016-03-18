<?php
namespace AppBundle\Manager;

class DiffTaxon extends AbstractDiff
{
    public $excludeFieldsName = ['created', 'modified', 'sourcefileid', 'dwcataxonid'];

    protected function getIdSetter()
    {
        return 'getTaxonid';
    }

    protected function getIdField()
    {
        return 'taxonid';
    }
}
