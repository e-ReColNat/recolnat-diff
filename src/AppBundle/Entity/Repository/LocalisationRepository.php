<?php

namespace AppBundle\Entity\Repository;
use Doctrine\ORM\Query\Expr\Join ;
/**
 * LocalisationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LocalisationRepository extends RecolnatRepositoryAbstract
{
    /**
     * 
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
                ->select('l')
                ->from('AppBundle\Entity\Localisation', 'l', 'l.locationid')
                ->where('l.locationid IN (:ids)')
                ->setParameter('ids', $ids)
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
        $qb = $this->createQueryBuilder('l');
        $query = $this->getEntityManager()->createQueryBuilder('l')
                ->select('l')
                ->addSelect($this->getExprConcatSpecimenCode($qb).' as specimenid')
                ->from('AppBundle\Entity\Specimen', 's')
                ->from('AppBundle\Entity\Recolte', 'r')
                ->from('AppBundle\Entity\Localisation', 'l')
                ->where($qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'))
                ->andWhere('s.recolte = r.eventid')
                ->andWhere('r.localisation = l.locationid')
                ;

        $query->setParameter('specimenCodes', $specimenCodes);
        return $this->orderResultSetBySpecimenId($query->getQuery()->getResult()) ;
    }
}
