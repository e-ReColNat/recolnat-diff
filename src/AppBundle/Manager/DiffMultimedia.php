<?php
namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;

class DiffMultimedia extends AbstractDiff
{
    public $excludeFieldsName = ['created', 'modified', 'discriminator'];

    protected function getIdSetter()
    {
        return 'getMultimediaid';
    }

    protected function getIdField()
    {
        return 'multimediaid';
    }
}
