<?php

namespace AppBundle\Entity\Repository;

/**
 * SpecimenRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SpecimenRepository extends RecolnatRepositoryAbstract
{
    /**
     * 
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
                ->select('s')
                ->from('AppBundle\Entity\Specimen', 's', 's.occurrenceid')
                ->where('s.occurrenceid IN (\''.implode('\',\'', $ids).'\')')
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
        $qb = $this->createQueryBuilder('s');
        
        $query = $qb
                ->select('s')
                ->addSelect($this->getExprConcatSpecimenCode($qb).' as specimenid');
        $qb->add('where', $qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        //$qb->getQuery()->setFetchMode('AppBundle\Specimen', 'determination', \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER);
        //$qb->getQuery()->setFetchMode('AppBundle\Specimen', 'recolte', \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER);
        return $this->orderResultSetBySpecimenId($query->getQuery()->getResult(), 'occurrenceid') ;
    }
}
