<?php
namespace AppBundle\Manager;

class DiffDetermination extends AbstractDiff
{

    public $excludeFieldsName = ['hascoordinates', 'sourcefileid', 'created', 'modified'];
}
