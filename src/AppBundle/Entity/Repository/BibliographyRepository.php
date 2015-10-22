<?php

namespace AppBundle\Entity\Repository;
use Doctrine\ORM\Query\Expr\Join ;
/**
 * BibliographyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BibliographyRepository extends RecolnatRepositoryAbstract
{
    /**
     * 
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
                ->select('b')
                ->from('AppBundle\Entity\Bibliography', 'b', 'b.referenceid')
                ->where('b.referenceid IN (\''.implode('\',\'', $ids).'\')')
                ->getQuery() ;
        return $query->getResult() ;
    }
    
    /**
     * 
     * @param array $specimenCodes
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes)
    {
        $qb = $this->createQueryBuilder('b');
        
        $query = $this->getEntityManager()->createQueryBuilder()
                ->select('b')
                ->from('AppBundle\Entity\Bibliography', 'b')
                ->addSelect($this->getExprConcatSpecimenCode($qb).' as specimenid')
                ->join('AppBundle\Entity\Specimen', 's', Join::WITH);
        $query->add('where', $qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'));
        $query->setParameter('specimenCodes', $specimenCodes);
        return $this->orderResultSetBySpecimenId($query->getQuery()->getResult()) ;
    }
    
}
