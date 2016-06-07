<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Collection;
use AppBundle\Entity\Repository\Abstracts\AbstractRecolnatRepository;
use Doctrine\ORM\AbstractQuery;


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
            ->from('AppBundle:Specimen', 's')
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
            ->from('AppBundle:Specimen', 's', 's.occurrenceid')
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
        $query = $this->getQbForFindByCatalogNumber($collection, [$catalogNumber]);

        return $query->getOneOrNullResult();
    }

    /**
     * @param Collection $collection
     * @param array      $catalogNumbers
     * @return array
     */
    public function findByCatalogNumbersUnordered(Collection $collection, $catalogNumbers)
    {
        $query = $this->getQbForFindByCatalogNumber($collection, $catalogNumbers);

        return $query->getArrayResult();
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
        $query = $this->getQbForFindByCatalogNumber($collection, $catalogNumbers);

        $query->useResultCache(true, 300);

        return $this->orderResultSetByCatalogNumber($query->getResult($hydratationMode), 'occurrenceid');
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

    /**
     * @param Collection $collection
     * @param array      $catalogNumbers
     * @return \Doctrine\ORM\Query
     */
    public function getQbForFindByCatalogNumber(Collection $collection, $catalogNumbers)
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
        $query = $qb->getQuery();

        return $query;
    }


    /**
     * @param string $collectionCode
     * @return \DateTime|null
     */
    public function getMinDate($collectionCode)
    {

        $date =  $this->createQueryBuilder('s')
            ->select('MIN(s.modified)')
            ->where('s.collectioncode = :collectionCode')
            ->setParameter('collectionCode', $collectionCode)
            ->getQuery()
            ->getSingleScalarResult() ;
        if (!is_null($date)) {
            return \DateTime::createFromFormat('Y-m-d H:i:s', $date) ;
        }
        return null;
    }
}
