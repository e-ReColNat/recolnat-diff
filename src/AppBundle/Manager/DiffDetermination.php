<?php

namespace AppBundle\Manager;

class DiffDetermination extends AbstractDiff
{

    public $excludeFieldsName = [
        'identificationid',
        'occurrenceid',
        'hascoordinates',
        'sourcefileid',
        'created',
        'modified',
        ];
}
