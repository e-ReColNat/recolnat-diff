<?php

namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Repository\RecolnatRepositoryAbstract;
use Doctrine\ORM\Query\Expr ;
use Symfony\Component\Translation\DataCollectorTranslator ;
use Symfony\Component\Intl\Locale ;
/**
 * Description of EntityManager
 *
 * @author tpateffoz
 */
class GenericEntityManager
{
    /**
     * @var EntityManager 
     */
    protected $emR;
    /**
     * @var EntityManager 
     */
    protected $emI;

    protected $translator ;
    protected $stats=array();
    protected $excludeFieldsName = [] ;
    
    public function __construct(EntityManager $emR, EntityManager $emI, DataCollectorTranslator $translator)
    {
        $this->emR = $emR;
        $this->emI = $emI;
        $this->translator = $translator;
    }
    
    public function getData($base, $className, $fieldName, $id)
    {
        $fullClassName = '\AppBundle\Entity\\'.$className ;
        $getter = 'get'.$fieldName;
        if (method_exists($fullClassName, $getter)) {
            $em = $this->emI ;
            if (strtolower($base) == 'recolnat') {
                $em = $this->emR ;
            }
            $entity = $em->getRepository($fullClassName)->find($id) ;
            
            $data = $entity->{$getter}() ;
            if ($data instanceof \DateTime) {
                $dateFormater = $this->getDateFormatter() ;
                $data = $dateFormater->format($data) ;
            }
            return $data;
        }
        else {
            throw new Exception('\AppBundle\Entity\\'.$className, 'get'.$fieldName.' doesn\'t exists.') ;
        }
    }
    private function getDateFormatter() 
    {
        return \IntlDateFormatter::create(
                    Locale::getDefault(), 
                    \IntlDateFormatter::SHORT, 
                    \IntlDateFormatter::NONE) ;
    }
}
