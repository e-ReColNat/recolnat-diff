<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;


class DiffSpecimen extends AbstractDiff
{
    public $excludeFieldsName = ['created', 'modified', 'dwcaid', 'hasmedia', 'sourcefileid', 'hascoordinates'];

    protected function getIdSetter()
    {
        return 'getOccurrenceid';
    }
}
