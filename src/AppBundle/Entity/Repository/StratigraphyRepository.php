<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Collection;

/**
 * StratigraphyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StratigraphyRepository extends RecolnatRepositoryAbstract
{
    /**
     * @param Collection $collection
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderFindByCollection(Collection $collection)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('st')
            ->from('AppBundle:Stratigraphy', 'st')
            ->join('st.specimen', 's')
            ->andWhere('s.collection = :collection')
            ->setParameter('collection', $collection);
    }
    /**
     *
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('s')
            ->from('AppBundle\Entity\Stratigraphy', 's', 's.geologicalcontextid')
            ->where('s.geologicalcontextid IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery();
        return $query->getResult();
    }

    public function findOneById($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('s')
            ->from('AppBundle\Entity\Stratigraphy', 's', 's.geologicalcontextid')
            ->where('s.geologicalcontextid = :id')
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
        $qb = $this->createQueryBuilder('st');

        $qb
            ->select('st')
            ->join('st.specimen', 's');
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $qb->getQuery()->getResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes)
    {
        $qb = $this->createQueryBuilder('st');

        $qb
            ->select('st')
            ->addSelect($this->getExprConcatSpecimenCode().' as specimencode')
            ->join('st.specimen', 's');
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $this->orderResultSetBySpecimenCode($qb->getQuery()->getResult(), 'geologicalcontextid');
    }
}
