<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

/**
 * @author tpateffoz
 */
class DiffLocalisation extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid'];

    protected function getIdSetter()
    {
        return 'getLocationid';
    }
}
