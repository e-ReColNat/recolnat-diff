<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

class DiffMultimedia extends DiffAbstract
{
    public $excludeFieldsName = ['created', 'modified', 'discriminator'];

    protected function getIdSetter()
    {
        return 'getMultimediaid';
    }
}
