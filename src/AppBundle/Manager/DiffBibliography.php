<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;


class DiffBibliography extends DiffAbstract
{
    public $excludeFieldsName = ['sourcefileid'];

    protected function getIdSetter()
    {
        return 'getReferenceId';
    }

}
