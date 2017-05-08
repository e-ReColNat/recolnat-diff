<?php
namespace AppBundle\Manager;


class DiffBibliography extends AbstractDiff
{
    public $excludeFieldsName = ['sourcefileid', 'created', 'modified'];
}
