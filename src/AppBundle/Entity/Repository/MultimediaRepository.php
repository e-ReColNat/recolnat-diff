<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Collection;
use Doctrine\ORM\AbstractQuery;

class MultimediaRepository extends AbstractRecolnatRepository
{

    /**
     * @param string $id
     * @param int   $fetchMode
     * @return array|object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById('multimedia', $id)->getOneOrNullResult($fetchMode);
    }

    /**
     * @param string $id
     * @return array|object|null
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
     * @param array $specimenCodes
     * @param $hydratationMode int
     * @return array
     */
    public function findBySpecimenCodes($specimenCodes, $hydratationMode = AbstractQuery::HYDRATE_ARRAY)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('m')
            ->addSelect($this->getExprConcatSpecimenCode().' as specimencode')
            ->from('AppBundle\Entity\Multimedia', 'm')
            ->innerJoin('m.specimens', 's')
            ->orderBy('m.identifier', 'ASC');
        $this->setSpecimenCodesWhereClause($qb, $specimenCodes);
        return $this->orderResultSetBySpecimenCode($qb->getQuery()->getResult($hydratationMode), 'multimediaid');
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
