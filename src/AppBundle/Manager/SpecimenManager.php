<?php

namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Repository\RecolnatRepositoryAbstract;
use Doctrine\ORM\Query\Expr ;
use Symfony\Component\Translation\DataCollectorTranslator ;
use Symfony\Component\Intl\Locale ;
/**
 * Description of SpecimenManager
 *
 * @author tpateffoz
 */
class SpecimenManager
{
    /**
     * Holds the Doctrine entity manager for eRecolnat database interaction
     * @var EntityManager 
     */
    protected $emR;
    /**
     * Holds the Doctrine entity manager for Institution database interaction
     * @var EntityManager 
     */
    protected $emI;
    
    /**
     * Holds the Doctrine entity manager for Institution database interaction
     * @var EntityManager 
     */
    protected $em;

    protected $translator ;
    protected $stats=array();
    protected $excludeFieldsName = [] ;
    
    public function __construct(EntityManager $emR, EntityManager $emI, DataCollectorTranslator $translator)
    {
        $this->emR = $emR;
        $this->emI = $emI;
        $this->translator = $translator;
    }
    
    public function init($base)
    {
        if (in_array($base, ['recolnat', 'institution'])) {
            if ($base == 'recolnat') {
                $this->em = $this->emR ;
            }
            else {
                $this->em = $this->emI ;
            }
        }
        else {
            throw new \Exception('La base doit être choisie : recolnat ou institution');
        }
        return $this;
    }
    
    public function getSpecimensWithBestDetermination($occurrenceIds = null, $specimenCodes = null) 
    {
        if (is_null($specimenCodes) && is_null($occurrenceIds)) {
            return null ;
        }
        $qb = $this->em->createQueryBuilder('s');

        $queryDql = 'SELECT s, d '
                . 'FROM AppBundle\Entity\Specimen s '
                . 'JOIN AppBundle\Entity\Determination d WITH '
                . 'd.identificationid IN ('
                    . 'SELECT d2.identificationid '
                    . 'FROM AppBundle\Entity\Determination d2, '
                    . 'AppBundle\Entity\Specimen s2 '
                    . 'WHERE '
                    . 'd2.specimen = s2 AND '
                    . RecolnatRepositoryAbstract::getExprConcatSpecimenCode($qb, 's2')
                    .' IN (:specimenCodes) '
                    . ') '
                . 'WHERE '
                . RecolnatRepositoryAbstract::getExprConcatSpecimenCode($qb)
                .' IN (:specimenCodes) '
                ;
        $query = $this->em->createQuery($queryDql) ;

        if (!is_null($specimenCodes)) {
                $query->setParameter('specimenCodes', $specimenCodes);
        }
        return $query->getResult() ;
    }
    
    public function getCsv(\AppBundle\Entity\Specimen $specimen, array $fieldsOrder) {
        $csvRow = [] ;
        $dateFormater = $this->getDateFormatter() ;

        foreach ($fieldsOrder as $field) {
            $getter = 'get'.$field ;
            if (method_exists($specimen, $getter)) {
                $value = $specimen->{$getter}() ;
                if ($value instanceof \DateTime) {
                    $value = $dateFormater->format($value) ;
                }
                $csvRow[$field] = $value;
            }
            else {
                throw new \Exception(sprintf('Methode %s n\'existe pas pour l\'entité %s', $specimen, $getter));
            }
            
        }
        return $csvRow ;
    }
    private function getDateFormatter() {
        return \IntlDateFormatter::create(
                    Locale::getDefault(), 
                    \IntlDateFormatter::SHORT, 
                    \IntlDateFormatter::NONE) ;
    }
}
