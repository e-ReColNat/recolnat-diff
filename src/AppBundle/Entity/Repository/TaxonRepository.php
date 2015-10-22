<?php

namespace AppBundle\Entity\Repository;
use Doctrine\ORM\Query\Expr\Join ;
/**
 * TaxonRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TaxonRepository extends RecolnatRepositoryAbstract
{
    /**
     * 
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
                ->select('t')
                ->from('AppBundle\Entity\Taxon', 't', 't.taxonid')
                ->where('t.taxonid IN (\''.implode('\',\'', $ids).'\')')
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
        /*$qb = $this->createQueryBuilder('t');
        
        $query = $qb
                ->select('t')
                ->addSelect($this->getExprConcatSpecimenCode($qb).' as specimenid')
                ->join('AppBundle\Entity\Determination', 'd', Join::WITH)
                ->join('AppBundle\Entity\Specimen', 's', Join::WITH);
        $qb->add('where', $qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        return $this->orderResultSetBySpecimenId($query->getQuery()->getResult()) ;*/
        $qb = $this->createQueryBuilder('t');
        
        $query = $this->getEntityManager()->createQueryBuilder('t')
           ->select('t')
            ->from('AppBundle\Entity\Taxon', 't')
            ->addSelect($this->getExprConcatSpecimenCode($qb).' as specimenid')
            ->innerJoin('t.determination', 'd')
            ->innerJoin('d.specimen', 's', \Doctrine\ORM\Query\Expr\Join::WITH, $qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'));
        $query->setParameter('specimenCodes', $specimenCodes);
        return $this->orderResultSetBySpecimenId($query->getQuery()->getResult()) ;
    }
}
