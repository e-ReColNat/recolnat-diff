<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

class DiffDetermination extends DiffAbstract
{

    public $excludeFieldsName = ['hascoordinates', 'verbatimcountry', 'sourcefileid', 'averagealtituderounded'];

    protected function getIdSetter()
    {
        return 'getIdentificationId';
    }

}
