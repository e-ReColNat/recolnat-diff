<?php

namespace AppBundle\Entity\Repository\Abstracts;

use AppBundle\Entity\Collection;
use Doctrine\ORM\AbstractQuery;

/**
 * LocalisationRepository
 */
abstract class AbstractLocalisationRepository extends AbstractRecolnatRepository
{
    /**
     * @param Collection $collection
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderFindByCollection(Collection $collection)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('l.locationid as id')
            ->from('localisation', 'l')
            ->join('l.recoltes', 'r')
            ->join('r.specimen', 's', 'WITH', 's.collection = :collection')
            ->setParameter('collection', $collection->getCollectionid());
    }

    /**
     *
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('l')
            ->from('localisation', 'l', 'l.locationid')
            ->where('l.locationid IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param Collection $collection
     * @param array      $catalogNumbers
     * @return array
     */
    public function findByCatalogNumbersUnordered(Collection $collection, $catalogNumbers)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('l')
            ->from('specimen', 's')
            ->from('recolte', 'r')
            ->from('localisation', 'l')
            ->andWhere('s.recolte = r.eventid')
            ->andWhere('r.localisation = l.locationid');

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
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('l')
            ->addSelect($this->getExprCatalogNumber().' as catalognumber')
            ->from('specimen', 's')
            ->from('recolte', 'r')
            ->from('localisation', 'l')
            ->andWhere('s.recolte = r.eventid')
            ->andWhere('r.localisation = l.locationid');

        $this->setSpecimenCodesWhereClause($collection, $qb, $catalogNumbers);

        return $this->orderResultSetByCatalogNumber($qb->getQuery()->getResult($hydratationMode), 'locationid');
    }

    /**
     * @param string $id
     * @param int    $fetchMode
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById('localisation', $id)->getOneOrNullResult($fetchMode);
    }

    /**
     * @param array  $datas
     * @param string $id
     * @return mixed
     */
    public function update(array $datas, $id)
    {
        $qb = $this->createUpdateQuery($datas);

        $qb->where('a.locationid = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->execute();
    }
}
