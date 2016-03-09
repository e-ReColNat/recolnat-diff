<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Collection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

class MultimediaRepository extends RecolnatRepositoryAbstract
{

    /**
     * @param array $id
     * @param int   $fetchMode
     * @return array|object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById('multimedia', $id)->getOneOrNullResult($fetchMode);
    }

    /**
     * @param $id
     * @return array|null
     */
    public function findOneByIdToArray($id)
    {
        return $this->findOneById($id, AbstractQuery::HYDRATE_ARRAY);
    }


    public function getQueryBuilderFindByCollection(Collection $collection)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('m')
            ->from('AppBundle\Entity\Multimedia', 'm')
            ->join('AppBundle\Entity\Specimen', 's')
            ->andWhere('s.collection = :collection')
            ->setParameter('collection', $collection);
    }


    /**
     * @param array $specimenCodesdiff
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('m')
            ->addSelect($this->getExprConcatSpecimenCode().' as specimencode')
            ->from('AppBundle\Entity\Multimedia', 'm')
            ->innerJoin('m.specimens', 's')
            ->orderBy('m.identifier', 'ASC');
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $this->orderResultSetBySpecimenCode($qb->getQuery()->getResult(), 'multimediaid');
    }

    /**
     * @param $specimenCodes
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findBySpecimenCodeUnordered($specimenCodes)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('m')
            ->from('AppBundle\Entity\Multimedia', 'm')
            ->join('AppBundle\Entity\Specimen', 's');
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('m')
            ->from('AppBundle\Entity\Multimedia', 'm')
            ->andWhere($qb->expr()->in('m.multimediaid', $ids));
        $qb->setParameter('ids', $ids, 'rawid');
        return $qb->getQuery()->getResult();
    }

    /**
     * @param array  $datas
     * @param string $id
     * @return mixed
     */
    public function update(array $datas, $id)
    {
        $qb = $this->createUpdateQuery($datas);

        $qb->where('a.multimediaid = HEXTORAW(:id)')
            ->setParameter('id', $id);
        return $qb->getQuery()->execute();
    }


}
