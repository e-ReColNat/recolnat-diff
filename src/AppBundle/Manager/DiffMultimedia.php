<?php
namespace AppBundle\Manager;

class DiffMultimedia extends AbstractDiff
{
    public $excludeFieldsName = ['created', 'modified', 'discriminator'];

}
