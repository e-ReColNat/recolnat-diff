<?php
namespace AppBundle\Manager;


class DiffStratigraphy extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid'];

    protected function getIdSetter()
    {
        return 'getGeologicalcontextid';
    }

    protected function getIdField()
    {
        return 'geologicalcontextid';
    }
}
