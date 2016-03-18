<?php
namespace AppBundle\Manager;

class DiffLocalisation extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid'];

    protected function getIdSetter()
    {
        return 'getLocationid';
    }

    protected function getIdField()
    {
        return 'locationid';
    }
}
