<?php
namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
/**
 * Description of DiffBibliographies
 *
 * @author tpateffoz
 */
class DiffLocalisation extends DiffAbstract
{
    protected function getIdSetter()
    {
        return 'getLocationid';
    }

}
