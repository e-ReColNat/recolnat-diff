<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Abstracts\AbstractRecolnatRepository;
use AppBundle\Manager\DiffLocalisation;
use Doctrine\ORM\AbstractQuery;


class LocalisationRepository extends AbstractRecolnatRepository
{
    public static function getEntityIdField()
    {
        return DiffLocalisation::getIdField();
    }

    public static function getSqlDiscriminationId() {
        return 'l.locationid';
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderJoinSpecimen()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('l')
            ->from('AppBundle:Specimen', 's')
            ->from('AppBundle:Recolte', 'r')
            ->from('AppBundle:Localisation', 'l')
            ->andWhere('s.recolte = r.eventid')
            ->andWhere('r.localisation = l.locationid');
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
            ->from('AppBundle:Localisation', 'l', 'l.locationid')
            ->where('l.locationid IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery();

        return $query->getResult();
    }


    /**
     * @param string $id
     * @param int    $fetchMode
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById('localisation', $id)->getOneOrNullResult($fetchMode);
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
