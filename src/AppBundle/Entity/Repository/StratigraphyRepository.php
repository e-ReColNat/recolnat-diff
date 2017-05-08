<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Abstracts\AbstractRecolnatRepository;
use AppBundle\Manager\DiffStratigraphy;
use Doctrine\ORM\AbstractQuery;

/**
 * StratigraphyRepository
 */
class StratigraphyRepository extends AbstractRecolnatRepository
{
    public static function getEntityIdField(){
        return DiffStratigraphy::getIdField();
    }

    public static function getSqlDiscriminationId() {
        return 'geologicalcontextid';
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderJoinSpecimen()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('st')
            ->from('AppBundle:Stratigraphy', 'st')
            ->join('st.specimen', 's');
    }

    /**
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('s')
            ->from('AppBundle\Entity\Stratigraphy', 's', 's.geologicalcontextid')
            ->where('s.geologicalcontextid IN (:ids)')
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
        return $this->getQueryFindOneById('stratigraphy', $id)->getOneOrNullResult($fetchMode);
    }

    /**
     * @param array  $datas
     * @param string $id
     * @return mixed
     */
    public function update(array $datas, $id)
    {
        $qb = $this->createUpdateQuery($datas);

        $qb->where('a.geologicalcontextid = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->execute();
    }
}
