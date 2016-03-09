<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

/**
 * @author tpateffoz
 */
class DiffLocalisation extends DiffAbstract
{
    protected function getIdSetter()
    {
        return 'getLocationid';
    }
}
