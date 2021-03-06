<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Abstracts\AbstractRecolnatRepository;
use AppBundle\Manager\DiffDetermination;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;

/**
 * DeterminationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DeterminationRepository extends AbstractRecolnatRepository
{
    public static function getEntityIdField()
    {
        return DiffDetermination::getIdField();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderJoinSpecimen()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('d')
            ->from('AppBundle:Determination', 'd')
            ->join('d.specimen', 's');
    }

    /**
     *
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('d')
            ->from('AppBundle\Entity\Determination', 'd', 'd.identificationid')
            ->andWhere($qb->expr()->in('d.identificationid', $ids));
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
        return $this->getQueryFindOneById('determination', $id)->getOneOrNullResult($fetchMode);
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
     *
     * @param string $occurrenceId
     * @return \AppBundle\Entity\Determination | null
     */
    public function findBestDetermination($occurrenceId)
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->select('d')
            ->join('AppBundle\Entity\Specimen', 's', Join::WITH, 's.occurrenceid = :occurrenceid');
        $qb->setParameter('occurrenceid', $occurrenceId, 'rawid');

        return $this->orderResultSetByCatalogNumberAndId($qb->getQuery()->getOneOrNullResult(), 'identificationid');
    }

    /**
     * @param array  $datas
     * @param string $id
     * @return mixed
     */
    public function update(array $datas, $id)
    {
        $qb = $this->createUpdateQuery($datas);

        $qb->where('a.identificationid = HEXTORAW(:id)')
            ->setParameter('id', $id);

        return $qb->getQuery()->execute();
    }
}
