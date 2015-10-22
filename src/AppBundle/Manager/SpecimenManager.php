<?php

namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Repository\RecolnatRepositoryAbstract;
use Doctrine\ORM\Query\Expr ;
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
    protected $emD;
    
    /**
     * Holds the Doctrine entity manager for Institution database interaction
     * @var EntityManager 
     */
    protected $em;

    protected $stats=array();
    protected $excludeFieldsName = [] ;
    
    public function __construct(EntityManager $emR, EntityManager $emD)
    {
        $this->emR = $emR;
        $this->emD = $emD;
    }
    
    public function init($base)
    {
        if (in_array($base, ['recolnat', 'institution'])) {
            if ($base == 'recolnat') {
                $this->em = $this->emR ;
            }
            else {
                $this->em = $this->emD ;
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
        $qb2 = $this->em->createQueryBuilder('d');
        
        /*$qb2->select('d')
                                ->from('AppBundle\Entity\Determination', 'd')
                                ->join('AppBundle\Entity\Specimen', 's', Expr\Join::WITH)
                                ->where($qb2->expr()->in(RecolnatRepositoryAbstract::getExprConcatSpecimenCode($qb2), ':specimenCodes'))
                                ->setParameter('specimenCodes', $specimenCodes)
                                ->orderBy('d.identificationverifstatus', 'DESC')
                                ->addOrderBy('d.dateidentified', 'DESC')
                                ->setMaxResults(1)
                            ->getQuery()->getResult();*/

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
                    //. ' ORDER BY d2.identificationverifstatus DESC '
                    . ') '
                . 'WHERE '
                . RecolnatRepositoryAbstract::getExprConcatSpecimenCode($qb)
                .' IN (:specimenCodes) '
                ;
        $query = $this->em->createQuery($queryDql) ;
        /*$query = $qb
                ->select('s, d')
                ->from('AppBundle\Entity\Specimen', 's')
                ->join('AppBundle\Entity\Determination', 'd')
                ->where(
                        $qb->expr()->in(
                                'd',
                                $qb2->select('d')
                                ->from('AppBundle\Entity\Determination', 'd')
                                ->join('AppBundle\Entity\Specimen', 's', Expr\Join::WITH)
                                ->where($qb2->expr()->in(RecolnatRepositoryAbstract::getExprConcatSpecimenCode($qb2), ':subSpecimenCodes'))
                                
                                ->orderBy('d.identificationverifstatus', 'DESC')
                                ->addOrderBy('d.dateidentified', 'DESC')
                                ->setMaxResults(1)
                                ->getDQL()
                                
                            ))
                 
                 ;*/
        if (!is_null($specimenCodes)) {
                //$query->add('where', $qb->expr()->in(RecolnatRepositoryAbstract::getExprConcatSpecimenCode($qb), ':specimenCodes'));
                $query->setParameter('specimenCodes', $specimenCodes);
                //$qb2->setParameter('subSpecimenCodes', $specimenCodes);
        }
        return $query->getResult() ;
    }
}
