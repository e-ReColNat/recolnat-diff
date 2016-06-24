<?php
namespace AppBundle\Manager;

class DiffSpecimen extends AbstractDiff
{
    public $excludeFieldsName = [
        'created',
        'modified',
        'dwcaid',
        'hasmedia',
        'sourcefileid',
        'hascoordinates',
        'explore_url'
    ];

    public static function getIdSetter()
    {
        return 'getOccurrenceid';
    }

    public static function getIdField()
    {
        return 'occurrenceid';
    }
}
