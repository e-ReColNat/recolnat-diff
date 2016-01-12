<?php

namespace AppBundle\Entity\Repository;
use Doctrine\ORM\Query\Expr\From;
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
    public function findOneById($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
                ->select('s')
                ->from('AppBundle\Entity\Specimen', 's', 's.occurrenceid')
                ->where('s.occurrenceid = :id')
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
        $qb = $this->createQueryBuilder('s');
        
        $query = $qb
                ->select('s');
                
        $qb->add('where', $qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        return $query->getQuery()->getResult();
    }
    
    /**
     * 
     * @param array $specimenCodes
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findAllBySpecimenCodeUnordered($specimenCodes)
    {
        $qb = $this->createQueryBuilder('s');
        
        $query = $qb
                ->select('s, b, d, t, m, st, r, l')
                ->leftJoin('s.bibliographies', 'b')
                ->leftJoin('s.determinations', 'd')
                ->leftJoin('d.taxon', 't')
                ->leftJoin('s.multimedias', 'm')
                ->leftJoin('s.stratigraphy', 'st')
                ->leftJoin('s.recolte', 'r')
                ->leftJoin('r.localisation', 'l')
                ;
                
        $qb->add('where', $qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        //return $query->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        return $query->getQuery()->getArrayResult();
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
                ->select('s, b, d, t, m, st, r, l')
                ->leftJoin('s.bibliographies', 'b')
                ->leftJoin('s.determinations', 'd')
                ->leftJoin('d.taxon', 't')
                ->leftJoin('s.multimedias', 'm')
                ->leftJoin('s.stratigraphy', 'st')
                ->leftJoin('s.recolte', 'r')
                ->leftJoin('r.localisation', 'l')
                //->add('from', new From('\AppBundle\Entity\Specimen', 's', 's.occurrenceid'), false)
                ->addSelect($this->getExprConcatSpecimenCode($qb).' as specimenid');
        $qb->add('where', $qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        //return $query->getQuery()->getResult() ;
        return $this->orderResultSetBySpecimenId($query->getQuery()->getResult(), 'occurrenceid') ;
    }

    /**
     * 
     * @param array $specimenCodes
     * @return array
     */
    public function getQueryForSpecimenCodes($specimenCodes)
    {
        $qb = $this->createQueryBuilder('s');
        
        $query = $qb
                ->select('s')
        ->add('where', $qb->expr()->in($this->getExprConcatSpecimenCode($qb), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        return $query->getQuery();
    }
}
