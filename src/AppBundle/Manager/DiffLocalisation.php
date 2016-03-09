<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

/**
 * @author tpateffoz
 */
class DiffLocalisation extends DiffAbstract
{
    public $excludeFieldsName = ['sourcefileid'];

    protected function getIdSetter()
    {
        return 'getLocationid';
    }
}
