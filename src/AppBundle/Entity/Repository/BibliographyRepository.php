<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Abstracts\AbstractRecolnatRepository;
use AppBundle\Manager\DiffBibliography;
use Doctrine\ORM\AbstractQuery;

/**
 * BibliographyRepository
 */
class BibliographyRepository extends AbstractRecolnatRepository
{

    public static function getEntityIdField()
    {
        return DiffBibliography::getIdField();
    }

    public static function getSqlDiscriminationId() {
        return 'b.title';
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderJoinSpecimen()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('b')
            ->from('AppBundle:Bibliography', 'b')
            ->join('b.specimen', 's');
    }

    /**
     *
     * @param array $ids
     * @return array
     */
    public function findById($ids)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('b')
            ->from('AppBundle:Bibliography', 'b', 'b.referenceid')
            ->andWhere($qb->expr()->in('b.referenceid', $ids));
        $qb->setParameter('ids', $ids, 'rawid');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $id
     * @param int    $fetchMode
     * @return array|object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($id, $fetchMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getQueryFindOneById('bibliography', $id)->getOneOrNullResult($fetchMode);
    }

    /**
     * @param string $id
     * @return array|object|null
     */
    public function findOneByIdToArray($id)
    {
        return $this->findOneById($id, AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param array  $datas
     * @param string $id
     * @return mixed
     */
    public function update(array $datas, $id)
    {
        $qb = $this->createUpdateQuery($datas);

        $qb->where('a.referenceid = HEXTORAW(:id)')
            ->setParameter('id', $id);

        return $qb->getQuery()->execute();
    }
}
