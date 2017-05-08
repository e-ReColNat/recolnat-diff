<?php
namespace AppBundle\Manager;

class DiffLocalisation extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid', 'created', 'modified'];

}
