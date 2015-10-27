<?php
namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
/**
 * Description of DiffStratigraphies
 *
 * @author tpateffoz
 */
class DiffStratigraphy extends DiffAbstract
{
    protected function getIdSetter()
    {
        return 'getGeologicalcontextid';
    }

}
