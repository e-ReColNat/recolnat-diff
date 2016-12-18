<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Collection;
use AppBundle\Entity\Repository\Abstracts\AbstractRecolnatRepository;
use AppBundle\Entity\Taxon;
use AppBundle\Manager\DiffTaxon;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\ResultSetMapping;

class TaxonRepository extends AbstractRecolnatRepository
{
    public static function getEntityIdField(){
        return DiffTaxon::getIdField();
    }

    public static function getSqlDiscriminationId() {
        return 'CONCAT(CONCAT(t.scientificname, \'#\'), t.scientificnameauthorship)';
    }
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderJoinSpecimen()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from('AppBundle:Taxon', 't')
            ->innerJoin('t.determination', 'd')
            ->join('d.specimen', 's');
    }

    /**
     *
     * @param string $ids
     * @return array
     */
    public function findById($ids)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
            ->from('AppBundle\Entity\Taxon', 't', 't.taxonid')
            ->andWhere($qb->expr()->in('t.taxonid', $ids));
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
        return $this->getQueryFindOneById('taxon', $id)->getOneOrNullResult($fetchMode);
    }

    /**
     * @param $id
     * @return array|object|null
     */
    public function findOneByIdToArray($id)
    {
        return $this->findOneById($id, AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param string $occurrenceId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findBestTaxon($occurrenceId)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->select('t')
            ->innerJoin('t.determination', 'd')
            ->where('d.specimen = :occurrenceid')
            ->setParameter('occurrenceid', $occurrenceId)
            ->setMaxResults(1)
            ->orderBy('d.identificationverifstatus', 'DESC');

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Collection   $collection
     * @param array|string $catalogNumbers
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findBestTaxonsByCatalogNumbers(Collection $collection, $catalogNumbers)
    {
        if (!is_array($catalogNumbers)) {
            $catalogNumbers = [$catalogNumbers];
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('scientificname', 'scientificname');
        $rsm->addScalarResult('scientificnameauthorship', 'scientificnameauthorship');
        $rsm->addScalarResult('catalognumber', 'catalognumber');
        $nativeSqlTaxon = '
        WITH FirstIDentified AS (
           SELECT First_Value(t.taxonid) OVER (PARTITION BY catalognumber ORDER BY identificationverifstatus) First,
           t.taxonid, t.scientificname, t.scientificnameauthorship,
           s.catalognumber
           FROM Taxons t
           JOIN Determinations d ON t.taxonid = d.taxonid
           JOIN Specimens s on d.occurrenceid = s.occurrenceid
           WHERE s.collectioncode = :collectionCode AND
           s.catalognumber IN (:catalogNumbers)
        )
        SELECT catalognumber, scientificname, scientificnameauthorship FROM FirstIdentified
        WHERE taxonid = First
        ';

        $this->getEntityManager()->getConnection()
            ->setFetchMode(\PDO::FETCH_ASSOC);
        $results = $this->getEntityManager()->getConnection()->executeQuery(
            $nativeSqlTaxon,
            [
                'collectionCode' => $collection->getCollectioncode(),
                'catalogNumbers' => $catalogNumbers
            ],
            [
                'catalogNumbers' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchAll();

        $formattedResult = [];
        if (count($results)) {
            foreach ($results as $values) {
                $formattedResult[$values['CATALOGNUMBER']] = Taxon::toString($values['SCIENTIFICNAME'],
                    $values['SCIENTIFICNAMEAUTHORSHIP']);
            }
        }

        return $formattedResult;
    }

    /**
     * @param array  $datas
     * @param string $id
     * @return mixed
     */
    public function update(array $datas, $id)
    {
        $qb = $this->createUpdateQuery($datas);

        $qb->where('a.taxonid = HEXTORAW(:id)')
            ->setParameter('id', $id);

        return $qb->getQuery()->execute();
    }
}
