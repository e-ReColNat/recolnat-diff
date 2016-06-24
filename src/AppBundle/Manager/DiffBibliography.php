<?php
namespace AppBundle\Manager;


class DiffBibliography extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid', 'created', 'modified'];

    public static function getIdSetter()
    {
        return 'getReferenceId';
    }

    public static function getIdField()
    {
        return 'referenceid';
    }
}
