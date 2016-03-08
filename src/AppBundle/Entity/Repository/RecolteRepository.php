<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Collection;

class RecolteRepository extends RecolnatRepositoryAbstract
{
    /**
     * @param Collection $collection
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderFindByCollection(Collection $collection)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('r.eventid as id')
            ->from('AppBundle\Entity\Recolte', 'r')
            ->join('r.specimen', 's')
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
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('AppBundle\Entity\Recolte', 'r', 'r.eventid')
            ->andWhere($qb->expr()->in('r.eventid', $ids));
        $qb->setParameter('ids', $ids, 'rawid');
        return $qb->getQuery()->getResult();
    }

    public function findOneById($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('r')
            ->from('AppBundle\Entity\Recolte', 'r', 'r.eventid')
            ->where('r.eventid = :id')
            ->setParameter('id', $id, 'rawid')
            ->getQuery();
        return $query->getOneOrNullResult();
    }

    /**
     * @param array  $id
     * @param string $field
     * @return object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneFieldById($id, $field)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('r.'.$field)
            ->from('AppBundle\Entity\Recolte', 'r')
            ->where('r.eventid = :id')
            ->setParameter('id', $id, 'rawid');
        return $qb->getQuery()->getArrayResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findBySpecimenCodeUnordered($specimenCodes)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('r')
            ->from('AppBundle\Entity\Recolte', 'r')
            ->join('AppBundle\Entity\Specimen', 's')
            ->andWhere('s.recolte = r.eventid');
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $qb->getQuery()->getResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('r')
            ->addSelect($this->getExprConcatSpecimenCode().' as specimencode')
            ->from('AppBundle\Entity\Recolte', 'r')
            ->join('AppBundle\Entity\Specimen', 's')
            ->andWhere('s.recolte = r.eventid');
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $this->orderResultSetBySpecimenCode($qb->getQuery()->getResult(), 'eventid');
    }

    /**
     * @param array  $datas
     * @param string $id
     * @return mixed
     */
    public function update(array $datas, $id)
    {
        $qb = $this->createUpdateQuery($datas);

        $qb->where('a.eventid = HEXTORAW(:id)')
            ->setParameter('id', $id);
        return $qb->getQuery()->execute();
    }
}
