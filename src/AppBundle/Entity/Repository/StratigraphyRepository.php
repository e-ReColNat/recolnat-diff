<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Collection;

/**
 * StratigraphyRepository
 */
class StratigraphyRepository extends AbstractRecolnatRepository
{
    /**
     * @param Collection $collection
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderFindByCollection(Collection $collection)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('st.geologicalcontextid as id')
            ->from('AppBundle:Stratigraphy', 'st')
            ->join('st.specimen', 's')
            ->andWhere('s.collection = :collection')
            ->setParameter('collection', $collection);
    }

    /**
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

    /**
     * @param array $id
     * @param int   $fetchMode
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById('stratigraphy', $id)->getOneOrNullResult($fetchMode);
    }

    /**
     *
     * @param array $specimenCodes
     * @return array
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
     * @param $hydratationMode int
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes, $hydratationMode = AbstractQuery::HYDRATE_ARRAY)
    {
        $qb = $this->createQueryBuilder('st');

        $qb
            ->select('st')
            ->addSelect($this->getExprConcatSpecimenCode().' as specimencode')
            ->join('st.specimen', 's');
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $this->orderResultSetBySpecimenCode($qb->getQuery()->getResult($hydratationMode), 'geologicalcontextid');
    }

    /**
     * @param array  $datas
     * @param string $id
     * @return mixed
     */
    public function update(array $datas, $id)
    {
        $qb = $this->createUpdateQuery($datas);

        $qb->where('a.geologicalcontextid = :id')
            ->setParameter('id', $id);
        return $qb->getQuery()->execute();
    }
}
