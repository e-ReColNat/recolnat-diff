<?php

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\AbstractQuery;

/**
 * BibliographyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BibliographyRepository extends RecolnatRepositoryAbstract
{
    /**
     *
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('b')
            ->from('AppBundle\Entity\Bibliography', 'b', 'b.referenceid')
            ->where('b.referenceid IN (\''.implode('\',\'', $ids).'\')')
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
            ->select('b')
            ->from('AppBundle\Entity\Bibliography', 'b', 'b.referenceid')
            ->where('b.referenceid = :id')
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
            ->select('b')
            ->from('AppBundle\Entity\Bibliography', 'b')
            ->join('b.specimen', 's');
        $qb->where($qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('b')
            ->from('AppBundle\Entity\Bibliography', 'b')
            ->addSelect($this->getExprConcatSpecimenCode().' as specimencode')
            ->join('b.specimen', 's');
        $qb->where($qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        return $this->orderResultSetBySpecimenCode($qb->getQuery()->getResult(), 'referenceid');
    }

}
