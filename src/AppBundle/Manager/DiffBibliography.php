<?php
namespace AppBundle\Manager;


class DiffBibliography extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid', 'created', 'modified'];

    protected function getIdSetter()
    {
        return 'getReferenceId';
    }

    protected function getIdField()
    {
        return 'referenceid';
    }
}
