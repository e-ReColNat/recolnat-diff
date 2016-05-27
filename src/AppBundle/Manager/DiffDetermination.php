<?php
namespace AppBundle\Manager;

class DiffDetermination extends AbstractDiff
{

    public $excludeFieldsName = ['hascoordinates', 'sourcefileid', 'created', 'modified'];

    protected function getIdSetter()
    {
        return 'getIdentificationId';
    }

    protected function getIdField()
    {
        return 'identificationid';
    }
}
