<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

class DiffRecolte extends DiffAbstract
{
    protected function getIdSetter()
    {
        return 'getEventid';
    }
}
