<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

class DiffRecolte extends DiffAbstract
{
    public $excludeFieldsName = ['eventdate', 'sourcefileid'];
    protected function getIdSetter()
    {
        return 'getEventid';
    }
}
