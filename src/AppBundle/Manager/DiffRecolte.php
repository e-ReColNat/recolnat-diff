<?php
namespace AppBundle\Manager;

class DiffRecolte extends AbstractDiff
{
    public $excludeFieldsName = ['eventdate', 'sourcefileid', 'averagealtituderounded', 'created', 'modified'];

    public static function getIdSetter()
    {
        return 'getEventid';
    }

    public static function getIdField()
    {
        return 'eventid';
    }
}
