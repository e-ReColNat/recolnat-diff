<?php

namespace AppBundle\Entity\Repository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;
/**
 * TaxonRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TaxonRepository extends RecolnatRepositoryAbstract
{
    /**
     * 
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
                ->select('t')
                ->from('AppBundle\Entity\Taxon', 't', 't.taxonid')
                ->where('t.taxonid IN (\''.implode('\',\'', $ids).'\')')
                ->getQuery();
        return $query->getResult();
    }

    /**
     * @param array $id
     * @param int   $fetchMode
     * @return array|object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode=AbstractQuery::HYDRATE_OBJECT)
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
    private function getQueryFindOneById($id) {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from('AppBundle\Entity\Taxon', 't', 't.taxonid')
            ->where('t.taxonid = :id')
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
        $qb = $this->createQueryBuilder('t');

        $query = $qb
            ->select('t')
            ->innerJoin('t.determination', 'd')
            ->innerJoin('d.specimen', 's', \Doctrine\ORM\Query\Expr\Join::WITH,
                    $qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCodes'));
        $query->setParameter('specimenCodes', $specimenCodes);
        return $query->getQuery()->getResult();
    }
    /**
     *
     * @param array $specimenCodes
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes)
    {
        $qb = $this->createQueryBuilder('t');

        $query = $qb
            ->select('t')
            ->addSelect($this->getExprConcatSpecimenCode().' as specimencode')
            ->innerJoin('t.determination', 'd')
            ->innerJoin('d.specimen', 's', Join::WITH, $qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCodes'));
        $query->setParameter('specimenCodes', $specimenCodes);
        return $this->orderResultSetBySpecimenCode($query->getQuery()->getResult(), 'taxonid');
    }

    /**
     * @param $occurrenceId
     * @return object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findBestTaxon($occurrenceId) {
        $qb = $this->createQueryBuilder('t');
        $query = $qb
                ->select('t')
                ->innerJoin('t.determination', 'd')
                ->where('d.specimen = :occurrenceid')
                ->setParameter('occurrenceid', $occurrenceId)
                ->setMaxResults(1)
                ->orderBy('d.identificationverifstatus', 'DESC');
        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $specimenCode
     * @return object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findBestTaxonsBySpecimenCode($specimenCode) {
        $qb = $this->createQueryBuilder('t');
        $query = $qb
                ->select('t')
                ->innerJoin('t.determination', 'd')
                ->innerJoin('d.specimen', 's', Join::WITH, $qb->expr()->in($this->getExprConcatSpecimenCode(), ':specimenCode'))
                ->setMaxResults(1)
                ->orderBy('d.identificationverifstatus', 'DESC');
        $query->setParameter('specimenCode', $specimenCode);
        return $query->getQuery()->getOneOrNullResult();
    }
}
