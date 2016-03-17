<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;


class DiffBibliography extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid'];

    protected function getIdSetter()
    {
        return 'getReferenceId';
    }

    protected function getIdField()
    {
        return 'referenceid';
    }
}
