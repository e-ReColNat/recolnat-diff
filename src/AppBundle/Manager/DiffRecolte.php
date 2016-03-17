<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

class DiffRecolte extends AbstractDiff
{
    public $excludeFieldsName = ['eventdate', 'sourcefileid', 'averagealtituderounded'];
    protected function getIdSetter()
    {
        return 'getEventid';
    }

    protected function getIdField()
    {
        return 'eventid';
    }
}
