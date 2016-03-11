<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Collection;
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
     * @param array $id
     * @param int   $fetchMode
     * @return object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById('specimen', $id)->getOneOrNullResult($fetchMode);
    }

    /**
     * @param $specimenCode
     * @return object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySpecimenCode($specimenCode)
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
        $this->setSpecimenCodesWhereClause($qb, [$specimenCode]);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return array
     */
    public function findBySpecimenCodeUnordered($specimenCodes)
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('s');
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $qb->getQuery()->getResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return array
     */
    public function findAllBySpecimenCodeUnordered($specimenCodes)
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
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $qb->getQuery()->getArrayResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes)
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
            ->leftJoin('r.localisation', 'l')
            ->addSelect($this->getExprConcatSpecimenCode().' as specimencode');
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        $query = $qb->getQuery();
        $query->useResultCache(true, 300);
        return $this->orderResultSetBySpecimenCode($query->getResult(), 'occurrenceid');
    }

    /**
     *
     * @param array $specimenCodes
     * @return Query
     */
    public function getQueryForSpecimenCodes($specimenCodes)
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('s');
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $qb->getQuery();
    }

    /**
     * @param array  $datas
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
