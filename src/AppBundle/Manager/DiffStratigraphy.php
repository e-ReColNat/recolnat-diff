<?php
namespace AppBundle\Manager;


class DiffStratigraphy extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid', 'created', 'modified'];

    public static function getIdSetter()
    {
        return 'getGeologicalcontextid';
    }

    public static function getIdField()
    {
        return 'geologicalcontextid';
    }
}
