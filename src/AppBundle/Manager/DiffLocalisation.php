<?php
namespace AppBundle\Manager;

class DiffLocalisation extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid', 'created', 'modified'];

    protected function getIdSetter()
    {
        return 'getLocationid';
    }

    protected function getIdField()
    {
        return 'locationid';
    }
}
