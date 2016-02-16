<?php

namespace AppBundle\Entity\Repository;

/**
 * SpecimenRepository
 *
 */
class SpecimenRepository extends RecolnatRepositoryAbstract
{
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
        $qb->setParameter('ids', $ids);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $id
     * @return object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('s')
            ->from('AppBundle\Entity\Specimen', 's', 's.occurrenceid')
            ->where('s.occurrenceid = :id')
            ->setParameter('id', $id)
            ->getQuery();
        return $qb->getOneOrNullResult();
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
        $qb->where($qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCodes'));
        $qb->setParameter('specimenCode', $specimenCode);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findBySpecimenCodeUnordered($specimenCodes)
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('s')
            ->where($qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        return $qb->getQuery()->getResult();
    }

    /**
     *
     * @param array $specimenCodes
     * @return \Doctrine\Common\Collections\Collection
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
        $qb->where($qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
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
        $qb->where($qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        $query = $qb->getQuery();
        $query->useResultCache('cache_key', 300);
        return $this->orderResultSetBySpecimenCode($query->getResult(), 'occurrenceid');
    }

    /**
     *
     * @param array $specimenCodes
     * @return array
     */
    public function getQueryForSpecimenCodes($specimenCodes)
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('s');
        $qb->where($qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCodes'));
        $qb->setParameter('specimenCodes', $specimenCodes);
        return $qb->getQuery();
    }
}
