<?php

namespace AppBundle\Entity\Repository\Abstracts;

use AppBundle\Entity\Collection;
use Doctrine\ORM\AbstractQuery;

/**
 * BibliographyRepository
 */
class AbstractBibliographyRepository extends AbstractRecolnatRepository
{
    /**
     * @param Collection $collection
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderFindByCollection(Collection $collection)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('b.referenceid as id')
            ->from('AppBundle:Bibliography', 'b')
            ->join('b.specimen', 's')
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
        $this->
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('b')
            ->from('AppBundle:Bibliography', 'b', 'b.referenceid')
            ->andWhere($qb->expr()->in('b.referenceid', $ids));
        $qb->setParameter('ids', $ids, 'rawid');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $id
     * @param int    $fetchMode
     * @return array|object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById('bibliography', $id)->getOneOrNullResult($fetchMode);
    }

    /**
     * @param string $id
     * @return array|object|null
     */
    public function findOneByIdToArray($id)
    {
        return $this->findOneById($id, AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param Collection $collection
     * @param array      $catalogNumbers
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByCatalogNumbersUnordered(Collection $collection, $catalogNumbers)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('b')
            ->from('AppBundle:Bibliography', 'b')
            ->join('b.specimen', 's');
        $this->setSpecimenCodesWhereClause($collection, $qb, $catalogNumbers);

        return $qb->getQuery()->getOneOrNullResult();
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
            ->select('b')
            ->from('AppBundle:Bibliography', 'b')
            ->addSelect($this->getExprCatalogNumber().' as catalognumber')
            ->join('b.specimen', 's');
        $this->setSpecimenCodesWhereClause($collection, $qb, $catalogNumbers);

        return $this->orderResultSetByCatalogNumber($qb->getQuery()->getResult($hydratationMode), 'referenceid');
    }

    /**
     * @param array  $datas
     * @param string $id
     * @return mixed
     */
    public function update(array $datas, $id)
    {
        $qb = $this->createUpdateQuery($datas);

        $qb->where('a.referenceid = HEXTORAW(:id)')
            ->setParameter('id', $id);

        return $qb->getQuery()->execute();
    }
}
