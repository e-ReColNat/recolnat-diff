<?php
namespace AppBundle\Manager;

class DiffTaxon extends AbstractDiff
{
    public $excludeFieldsName = ['created', 'modified', 'sourcefileid', 'dwcataxonid'];

}
