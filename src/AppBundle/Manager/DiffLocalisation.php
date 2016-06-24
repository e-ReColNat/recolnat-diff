<?php
namespace AppBundle\Manager;

class DiffLocalisation extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid', 'created', 'modified'];

    public static function getIdSetter()
    {
        return 'getLocationid';
    }

    public static function getIdField()
    {
        return 'locationid';
    }
}
