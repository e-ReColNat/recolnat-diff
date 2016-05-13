<?php

namespace AppBundle\Entity\Repository\Abstracts;

use AppBundle\Entity\Collection;
use Doctrine\ORM\AbstractQuery;

/**
 * StratigraphyRepository
 */
abstract class AbstractStratigraphyRepository extends AbstractRecolnatRepository
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
     * @param string $id
     * @param int    $fetchMode
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById('stratigraphy', $id)->getOneOrNullResult($fetchMode);
    }

    /**
     * @param Collection $collection
     * @param array      $catalogNumbers
     * @return array
     */
    public function findByCatalogNumbersUnordered(Collection $collection, $catalogNumbers)
    {
        $qb = $this->createQueryBuilder('st');

        $qb
            ->select('st')
            ->join('st.specimen', 's');
        $this->setSpecimenCodesWhereClause($collection, $qb, $catalogNumbers);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Collection $collection
     * @param array      $catalogNumbers
     * @param int        $hydratationMode
     * @return array
     */
    public function findByCatalogNumbers(
        Collection $collection,
        $catalogNumbers,
        $hydratationMode = AbstractQuery::HYDRATE_ARRAY
    ) {
        $qb = $this->createQueryBuilder('st');

        $qb
            ->select('st')
            ->addSelect($this->getExprCatalogNumber().' as catalognumber')
            ->join('st.specimen', 's');
        $this->setSpecimenCodesWhereClause($collection, $qb, $catalogNumbers);

        return $this->orderResultSetByCatalogNumber($qb->getQuery()->getResult($hydratationMode),
            'geologicalcontextid');
    }

    /**
     * @param array $datas
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
