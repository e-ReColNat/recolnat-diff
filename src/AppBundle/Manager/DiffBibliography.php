<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;


class DiffBibliography extends DiffAbstract
{
    protected function getIdSetter()
    {
        return 'getReferenceId';
    }

}
