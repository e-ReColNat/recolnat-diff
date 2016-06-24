<?php
namespace AppBundle\Manager;

class DiffDetermination extends AbstractDiff
{

    public $excludeFieldsName = ['hascoordinates', 'sourcefileid', 'created', 'modified'];

    public static function getIdSetter()
    {
        return 'getIdentificationId';
    }

    public static function getIdField()
    {
        return 'identificationid';
    }
}
