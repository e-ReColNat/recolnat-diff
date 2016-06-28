<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Abstracts\AbstractRecolnatRepository;
use AppBundle\Manager\DiffMultimedia;
use Doctrine\ORM\AbstractQuery;

class MultimediaRepository extends AbstractRecolnatRepository
{
    public static function getEntityIdField()
    {
        return DiffMultimedia::getIdField();
    }

    /**
     * @param string $id
     * @param int    $fetchMode
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


    public function getQueryBuilderJoinSpecimen()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('m')
            ->from('AppBundle\Entity\Multimedia', 'm')
            ->innerJoin('m.specimens', 's')
            ->orderBy('m.identifier', 'ASC');
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
