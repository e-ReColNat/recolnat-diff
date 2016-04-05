<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Collection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;

/**
 * SpecimenRepository
 *
 */
class SpecimenRepository extends AbstractRecolnatRepository
{
    /**
     * @param Collection $collection
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderFindByCollection(Collection $collection)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('s.occurrenceid as id')
            ->from('AppBundle\Entity\Specimen', 's')
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
        $qb = $this->createQueryBuilder('s');
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('s')
            ->from('AppBundle\Entity\Specimen', 's', 's.occurrenceid')
            ->where($qb->expr()->in('s.occurrenceid', ':ids'));
        $qb->setParameter('ids', $ids, 'rawid');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $id
     * @param int    $fetchMode
     * @return object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById('specimen', $id)->getOneOrNullResult($fetchMode);
    }

    /**
     * @param Collection $collection ,
     * @param string     $catalogNumber
     * @return object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByCatalogNumber(Collection $collection, $catalogNumber)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->select('s, b, d, t, m, st, r, l')
            ->leftJoin('s.bibliographies', 'b')
            ->leftJoin('s.determinations', 'd')
            ->leftJoin('d.taxon', 't')
            ->leftJoin('s.multimedias', 'm')
            ->leftJoin('s.stratigraphy', 'st')
            ->leftJoin('s.recolte', 'r')
            ->leftJoin('r.localisation', 'l');
        $this->setSpecimenCodesWhereClause($collection, $qb, [$catalogNumber]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Collection $collection
     * @param array      $catalogNumbers
     * @return array
     */
    public function findByCatalogNumbersUnordered(Collection $collection, $catalogNumbers)
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('s, b, d, t, m, st, r, l')
            ->leftJoin('s.bibliographies', 'b')
            ->leftJoin('s.determinations', 'd')
            ->leftJoin('d.taxon', 't')
            ->leftJoin('s.multimedias', 'm')
            ->leftJoin('s.stratigraphy', 'st')
            ->leftJoin('s.recolte', 'r')
            ->leftJoin('r.localisation', 'l');
        $this->setSpecimenCodesWhereClause($collection, $qb, $catalogNumbers);

        return $qb->getQuery()->getArrayResult();
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
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('s, b, d, t, m, st, r, l')
            ->leftJoin('s.bibliographies', 'b')
            ->leftJoin('s.determinations', 'd')
            ->leftJoin('d.taxon', 't')
            ->leftJoin('s.multimedias', 'm')
            ->leftJoin('s.stratigraphy', 'st')
            ->leftJoin('s.recolte', 'r')
            ->leftJoin('r.localisation', 'l');
        $this->setSpecimenCodesWhereClause($collection, $qb, $catalogNumbers);
        $query = $qb->getQuery();

        $query->useResultCache(true, 300);
        return $this->orderResultSetByCatalogNumber($query->getResult($hydratationMode), 'occurrenceid');
    }


    /**
     * @param array $datas
     * @param string $id
     * @return mixed
     */
    public function update(array $datas, $id)
    {
        $qb = $this->createUpdateQuery($datas);

        $qb->where('a.occurrenceid = HEXTORAW(:id)')
            ->setParameter('id', $id);

        return $qb->getQuery()->execute();
    }
}
