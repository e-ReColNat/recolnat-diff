<?php
namespace AppBundle\Manager;

class DiffRecolte extends AbstractDiff
{
    public $excludeFieldsName = ['eventdate', 'sourcefileid', 'averagealtituderounded', 'created', 'modified'];

}
