<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Collection;
use AppBundle\Entity\Repository\Abstracts\AbstractRecolnatRepository;
use AppBundle\Manager\DiffSpecimen;
use Doctrine\ORM\AbstractQuery;


class SpecimenRepository extends AbstractRecolnatRepository
{
    public static function getEntityIdField()
    {
        return DiffSpecimen::getIdField();
    }

    public static function getSqlDiscriminationId() {
        return 'CONCAT(CONCAT(s.collectioncode, \'#\'), s.catalognumber)';
    }

    public function getQueryBuilderJoinSpecimenForResearch() {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('s');

        return $qb;
    }
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderJoinSpecimen()
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

        return $qb;
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
        $qb = $this->getQueryBuilderJoinSpecimen();
        $this->setSpecimenCodesWhereClause($collection, $qb, [$catalogNumber]);

        return $qb->getQuery()->getOneOrNullResult();
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
     * @param string $collectionCode
     * @return \DateTime|null
     */
    public function getMinDate($collectionCode)
    {

        $date = $this->createQueryBuilder('s')
            ->select('MIN(s.modified)')
            ->where('s.collectioncode = :collectionCode')
            ->setParameter('collectionCode', $collectionCode)
            ->getQuery()
            ->getSingleScalarResult();
        if (!is_null($date)) {
            return \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        }

        return null;
    }
}
