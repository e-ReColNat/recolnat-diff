<?php
namespace AppBundle\Manager;

class DiffSpecimen extends AbstractDiff
{
    public $excludeFieldsName = ['created', 'modified', 'dwcaid', 'hasmedia', 'sourcefileid', 'hascoordinates'];

    protected function getIdSetter()
    {
        return 'getOccurrenceid';
    }

    protected function getIdField()
    {
        return 'occurrenceid';
    }
}
