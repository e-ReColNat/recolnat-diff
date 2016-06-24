<?php
namespace AppBundle\Manager;

class DiffMultimedia extends AbstractDiff
{
    public $excludeFieldsName = ['created', 'modified', 'discriminator'];

    public static function getIdSetter()
    {
        return 'getMultimediaid';
    }

    public static function getIdField()
    {
        return 'multimediaid';
    }
}
