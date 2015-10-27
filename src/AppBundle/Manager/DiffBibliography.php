<?php
namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
/**
 * Description of DiffBibliographies
 *
 * @author tpateffoz
 */
class DiffBibliography extends DiffAbstract
{
    protected function getIdSetter()
    {
        return 'getReferenceId';
    }

}
