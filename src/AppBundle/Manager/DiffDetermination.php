<?php
namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
/**
 * Description of DiffBibliographies
 *
 * @author tpateffoz
 */
class DiffDetermination extends DiffAbstract
{

    public $excludeFieldsName = ['created', 'modified'] ;
    
    protected function getIdSetter()
    {
        return 'getIdentificationId';
    }

}
