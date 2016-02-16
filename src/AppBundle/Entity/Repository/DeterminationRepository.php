<?php

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;

/**
 * DeterminationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DeterminationRepository extends RecolnatRepositoryAbstract
{
    /**
     *
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('d')
            ->from('AppBundle\Entity\Determination', 'd', 'd.identificationid')
            ->where('d.identificationid IN (\''.implode('\',\'', $ids).'\')')
            ->getQuery();
        return $query->getResult();
    }

    /**
     * @param array $id
     * @param int   $fetchMode
     * @return array|object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById($id)->getOneOrNullResult($fetchMode);
    }

    /**
     * @param $id
     * @return array|null
     */
    public function findOneByIdToArray($id)
    {

        return $this->findOneById($id, AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param $id
     * @return \Doctrine\ORM\Query
     */
    private function getQueryFindOneById($id)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('d')
            ->from('AppBundle\Entity\Determination', 'd', 'd.identificationid')
            ->where('d.identificationid = :id')
            ->setParameter('id', $id)
            ->getQuery();
    }

    /**
     *
     * @param array $specimenCodes
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findBySpecimenCodeUnordered($specimenCodes)
    {

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('d')
            ->join('d.specimen', 's');

        $qb->where($qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        return $qb->getQuery()->getResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes)
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->select('d')
            ->addSelect($this->getExprConcatSpecimenCode().' as specimencode')
            ->join('d.specimen', 's');
        $qb->where($qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        return $this->orderResultSetBySpecimenCode($qb->getQuery()->getResult(), 'identificationid');
    }

    /**
     *
     * @param rawid $occurrenceId
     * @return \AppBundle\Entity\Determination | null
     */
    public function findBestDetermination($occurrenceId)
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->select('d')
            ->join('AppBundle\Entity\Specimen', 's', Join::WITH, 's.occurrenceid = :occurrenceid');
        $qb->setParameter('occurrenceid', $occurrenceId);
        return $this->orderResultSetBySpecimenCode($qb->getQuery()->getOneOrNullResult(), 'identificationid');
    }
}
