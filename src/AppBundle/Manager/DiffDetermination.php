<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

class DiffDetermination extends DiffAbstract
{

    public $excludeFieldsName = ['created', 'modified'];

    protected function getIdSetter()
    {
        return 'getIdentificationId';
    }

}
