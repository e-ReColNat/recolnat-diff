<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

class DiffDetermination extends AbstractDiff
{

    public $excludeFieldsName = ['hascoordinates', 'verbatimcountry', 'sourcefileid', 'averagealtituderounded'];

    protected function getIdSetter()
    {
        return 'getIdentificationId';
    }

}
