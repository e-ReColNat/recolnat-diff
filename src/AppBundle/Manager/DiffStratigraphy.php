<?php
namespace AppBundle\Manager;


class DiffStratigraphy extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid', 'created', 'modified'];

}
