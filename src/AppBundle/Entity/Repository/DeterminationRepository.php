<?php

namespace AppBundle\Entity\Repository;
use Doctrine\ORM\Query\Expr\Join ;
/**
 * DeterminationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DeterminationRepository extends RecolnatRepositoryAbstract
{
    /**
     * 
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
                ->select('d')
                ->from('AppBundle\Entity\Determination', 'd', 'd.identificationid')
                ->where('d.identificationid IN (\''.implode('\',\'', $ids).'\')')
                ->getQuery() ;
        return $query->getResult() ;
    }
    
    public function findOneById($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
                ->select('d')
                ->from('AppBundle\Entity\Determination', 'd', 'd.identificationid')
                ->where('d.identificationid = :id')
                ->setParameter('id', $id)
                ->getQuery() ;
        return $query->getOneOrNullResult();
    }
    /**
     * 
     * @param array $specimenCodes
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findBySpecimenCodeUnordered($specimenCodes)
    {
        $qb = $this->createQueryBuilder('d');
        
        $query = $qb
                ->select('d')
                ->join('d.specimen', 's', Join::WITH);
        $query->add('where', $qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'));
        $query->setParameter('specimenCodes', $specimenCodes);
        return $query->getQuery()->getResult();
    }
    /**
     * 
     * @param array $specimenCodes
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes)
    {
        $qb = $this->createQueryBuilder('d');
        
        $query = $qb
                ->select('d')
                ->addSelect($this->getExprConcatSpecimenCode($qb).' as specimenid')
                ->join('d.specimen', 's', Join::WITH);
        $query->add('where', $qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'));
        $query->setParameter('specimenCodes', $specimenCodes);
        return $this->orderResultSetBySpecimenId($query->getQuery()->getResult(), 'identificationid') ;
    }
    
    /**
     * 
     * @param rawid $occurrenceId
     * @return \AppBundle\Entity\Determination | null
     */
    public function findBestDetermination($occurrenceId)
    {
        $qb = $this->createQueryBuilder('d');
        
        $query = $qb
                ->select('d')
                ->join('AppBundle\Entity\Specimen', 's', Join::WITH, 's.occurrenceid = :occurrenceid');
        $query->setParameter('occurrenceid', $occurrenceId);
        return $this->orderResultSetBySpecimenId($query->getQuery()->getOneOrNullResult()) ;
    }
}
