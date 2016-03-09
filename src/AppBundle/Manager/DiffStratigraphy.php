<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;


class DiffStratigraphy extends DiffAbstract
{
    public $excludeFieldsName = ['sourcefileid'];

    protected function getIdSetter()
    {
        return 'getGeologicalcontextid';
    }

}
