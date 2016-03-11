<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

class DiffRecolte extends AbstractDiff
{
    public $excludeFieldsName = ['eventdate', 'sourcefileid'];
    protected function getIdSetter()
    {
        return 'getEventid';
    }
}
