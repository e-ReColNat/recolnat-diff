<?php
namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
/**
 * Description of DiffBibliographies
 *
 * @author tpateffoz
 */
class DiffRecolte extends DiffAbstract
{
    protected function getIdSetter()
    {
        return 'getEventid';
    }

}
