<?php

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CollectionsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CollectionRepository extends EntityRepository
{
    public function findAllOrderByInstitution()
    {
        return $this->createQueryBuilder('c')
            ->join('c.institution', 'i')
            ->orderBy('i.institutioncode')
            ->getQuery()->getResult();
    }

    public function findOneByCollectionAndInstitution($institutionCode, $collectionCode)
    {
        return $this->createQueryBuilder('c')
            ->join('c.institution', 'i')
            ->andWhere('i.institutioncode = :institutionCode')
            ->andWhere('c.collectioncode = :collectionCode')
            ->setParameter(':institutionCode', $institutionCode)
            ->setParameter(':collectionCode', $collectionCode)
            ->getQuery()->getOneOrNullResult();
    }

}
