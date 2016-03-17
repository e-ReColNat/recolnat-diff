<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

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
