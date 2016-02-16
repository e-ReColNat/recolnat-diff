<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;


class DiffStratigraphy extends DiffAbstract
{
    protected function getIdSetter()
    {
        return 'getGeologicalcontextid';
    }

}
