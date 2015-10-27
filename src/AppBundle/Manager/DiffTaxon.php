<?php
namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
/**
 * Description of DiffTaxons
 *
 * @author tpateffoz
 */
class DiffTaxon extends DiffAbstract
{
    public $excludeFieldsName = ['created', 'modified'] ;
    
    protected function getIdSetter()
    {
        return 'getTaxonid';
    }

}
