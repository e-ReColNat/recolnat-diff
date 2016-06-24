<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Abstracts\AbstractRecolnatRepository;
use AppBundle\Manager\DiffRecolte;
use Doctrine\ORM\AbstractQuery;

class RecolteRepository extends AbstractRecolnatRepository
{
    public static function getEntityIdField(){
        return DiffRecolte::getIdField();
    }
    /**
     * @param string $id
     * @param int    $fetchMode
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById('recolte', $id)->getOneOrNullResult($fetchMode);
    }


    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderJoinSpecimen()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('r')
            ->from('AppBundle:Recolte', 'r')
            ->join('r.specimen', 's')
            ->andWhere('s.recolte = r.eventid');
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
            ->from('AppBundle:Recolte', 'r', 'r.eventid')
            ->andWhere($qb->expr()->in('r.eventid', $ids));
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

        $qb->where('a.eventid = HEXTORAW(:id)')
            ->setParameter('id', $id);

        return $qb->getQuery()->execute();
    }
}
