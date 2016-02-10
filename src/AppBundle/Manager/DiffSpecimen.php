<?php
namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
/**
 * Description of DiffSpecimens
 *
 * @author tpateffoz
 */
class DiffSpecimen extends DiffAbstract
{
    public $excludeFieldsName = ['created', 'modified', 'dwcaid', 'hasmedia'];
    
    protected function getIdSetter() 
    {
        return 'getOccurrenceid';
    }
}
