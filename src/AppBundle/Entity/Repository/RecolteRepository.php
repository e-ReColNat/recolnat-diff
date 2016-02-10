<?php

namespace AppBundle\Entity\Repository;
use Doctrine\ORM\Query\Expr\Join;
/**
 * RecolteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RecolteRepository extends RecolnatRepositoryAbstract
{
    /**
     * 
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
                ->select('r')
                ->from('AppBundle\Entity\Recolte', 'r', 'r.eventid')
                ->where('r.eventid IN (\''.implode('\',\'', $ids).'\')')
                ->getQuery();
        return $query->getResult();
    }
    public function findOneById($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
                ->select('r')
                ->from('AppBundle\Entity\Recolte', 'r', 'r.eventid')
                ->where('r.eventid = :id')
                ->setParameter('id', $id)
                ->getQuery();
        return $query->getOneOrNullResult();
    }
        /**
         * 
         * @param array $specimenCodes
         * @return \Doctrine\Common\Collections\Collection
         */
    public function findBySpecimenCodeUnordered($specimenCodes)
    {
        $qb = $this->createQueryBuilder('r');
        
        $query = $this->getEntityManager()->createQueryBuilder('r')
                ->select('r')
                ->from('AppBundle\Entity\Specimen', 's')
                ->from('AppBundle\Entity\Recolte', 'r')
                ->where($qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'))
                ->andWhere('s.recolte = r.eventid')
                ;
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
        $qb = $this->createQueryBuilder('r');
        
        $query = $this->getEntityManager()->createQueryBuilder('r')
                ->select('r')
                ->addSelect($this->getExprConcatSpecimenCode($qb).' as specimencode')
                ->from('AppBundle\Entity\Specimen', 's')
                ->from('AppBundle\Entity\Recolte', 'r')
                ->where($qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'))
                ->andWhere('s.recolte = r.eventid')
                ;
        $query->setParameter('specimenCodes', $specimenCodes);
        return $this->orderResultSetBySpecimenCode($query->getQuery()->getResult(), 'eventid');
    }
}
