<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

class DiffTaxon extends DiffAbstract
{
    public $excludeFieldsName = ['created', 'modified'];

    protected function getIdSetter()
    {
        return 'getTaxonid';
    }
}
