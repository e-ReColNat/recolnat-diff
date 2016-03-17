<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

class DiffDetermination extends AbstractDiff
{

    public $excludeFieldsName = ['hascoordinates', 'sourcefileid'];

    protected function getIdSetter()
    {
        return 'getIdentificationId';
    }

    protected function getIdField()
    {
        return 'identificationid';
    }
}
