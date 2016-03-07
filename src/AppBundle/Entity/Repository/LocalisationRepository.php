<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Collection;

/**
 * LocalisationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LocalisationRepository extends RecolnatRepositoryAbstract
{
    /**
     * @param Collection $collection
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderFindByCollection(Collection $collection)
    {
        return $this->getEntityManager()->createQueryBuilder('l')
            ->select('l.locationid as id')
            ->from('AppBundle\Entity\Localisation', 'l')
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
            ->from('AppBundle\Entity\Localisation', 'l', 'l.locationid')
            ->where('l.locationid IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery();
        return $query->getResult();
    }

    public function findOneById($id)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('l')
            ->from('AppBundle\Entity\Localisation', 'l', 'l.locationid')
            ->where('l.locationid = :id')
            ->setParameter('id', $id)
            ->getQuery();
        return $query->getOneOrNullResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findBySpecimenCodeUnordered($specimenCodes)
    {
        $qb = $this->createQueryBuilder('l');
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('l')
            ->from('AppBundle\Entity\Specimen', 's')
            ->from('AppBundle\Entity\Recolte', 'r')
            ->from('AppBundle\Entity\Localisation', 'l')
            ->andWhere('s.recolte = r.eventid')
            ->andWhere('r.localisation = l.locationid');

        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $query->getQuery()->getResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes)
    {
        $qb = $this->createQueryBuilder('l');
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('l')
            ->addSelect($this->getExprConcatSpecimenCode().' as specimencode')
            ->from('AppBundle\Entity\Specimen', 's')
            ->from('AppBundle\Entity\Recolte', 'r')
            ->from('AppBundle\Entity\Localisation', 'l')
            ->andWhere('s.recolte = r.eventid')
            ->andWhere('r.localisation = l.locationid');

        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $this->orderResultSetBySpecimenCode($query->getQuery()->getResult(), 'locationid');
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
