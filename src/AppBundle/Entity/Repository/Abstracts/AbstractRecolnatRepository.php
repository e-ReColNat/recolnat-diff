<?php
namespace AppBundle\Entity\Repository\Abstracts;

use AppBundle\Entity\Collection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * Description of RecolnatRepository
 *
 * @author tpateffoz
 */
abstract class AbstractRecolnatRepository extends EntityRepository
{
    const ENTITY_DESCR = [
        'bibliography' => ['rawid' => true, 'identifier' => 'referenceid'],
        'determination' => ['rawid' => true, 'identifier' => 'identificationid'],
        'localisation' => ['rawid' => false, 'identifier' => 'locationid'],
        'multimedia' => ['rawid' => true, 'identifier' => 'multimediaid'],
        'recolte' => ['rawid' => true, 'identifier' => 'eventid'],
        'specimen' => ['rawid' => true, 'identifier' => 'occurrenceid'],
        'stratigraphy' => ['rawid' => false, 'identifier' => 'geologicalcontextid'],
        'taxon' => ['rawid' => true, 'identifier' => 'taxonid'],
    ];
    const ENTITY_PREFIX = 'AppBundle\\Entity\\';

    public static function getEntityIdField()
    {
        throw new \LogicException('method getEntityIdField must be implemented');
    }

    /**
     * @param Collection $collection
     * @param array      $catalogNumbers
     * @param int        $hydratationMode int
     * @return array
     * @internal param array $specimenCodes
     */
    public function findByCatalogNumbers(
        Collection $collection,
        $catalogNumbers,
        $hydratationMode = AbstractQuery::HYDRATE_ARRAY
    ) {
        $qb = $this->getQueryBuilderJoinSpecimen();
        if ($hydratationMode == AbstractQuery::HYDRATE_OBJECT) {
            $qb->addSelect('s');
        } elseif ($this->getEntityName() != 'Specimen') {
            $qb->addSelect($this->getExprCatalogNumber().' as catalognumber');
        }
        $this->setSpecimenCodesWhereClause($collection, $qb, $catalogNumbers);
        return $this->orderResultSetByCatalogNumber($qb->getQuery()->getResult($hydratationMode));
    }

    /**
     * @param Collection $collection
     * @param array      $catalogNumbers
     * @return mixed
     */
    public function findByCatalogNumbersAndId(
        Collection $collection,
        $catalogNumbers,
        $hydratationMode = AbstractQuery::HYDRATE_ARRAY
    ) {

        $qb = $this->getQueryBuilderJoinSpecimen();
        $qb->addSelect($this->getExprCatalogNumber().' as catalognumber');
        $this->setSpecimenCodesWhereClause($collection, $qb, $catalogNumbers);

        return $this->orderResultSetByCatalogNumberAndId($qb->getQuery()->getResult($hydratationMode),
            $this->getEntityIdField());
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    abstract public function getQueryBuilderJoinSpecimen();

    /**
     *
     * @param array $ids
     * @return array
     */
    abstract public function findById($ids);

    /**
     *
     * @param string $id
     */
    abstract public function findOneById($id);

    /**
     * @param string $alias
     * @return Expr\Func
     */

    /**
     * @param array  $datas
     * @param string $id
     * @return mixed
     */
    abstract public function update(array $datas, $id);

    public static function getExprCatalogNumber($alias = 's')
    {
        return sprintf('%s.catalognumber', $alias);
    }


    /**
     * @param array  $resultsSet
     * @param string $identifierName
     * @return array
     */
    protected function orderResultSetByCatalogNumberAndId($resultsSet, $identifierName)
    {
        $orderResultSet = [];
        if (count($resultsSet)) {
            foreach ($resultsSet as $resultRow) {
                if (!empty($resultRow)) {
                    if (is_array($resultRow)) {
                        $row = $resultRow;
                        if (!isset($resultRow[$identifierName])) {
                            $row = $resultRow[0];
                        }
                        $catalogNumber = $resultRow['catalognumber'];
                        $orderResultSet[$catalogNumber][$row[$identifierName]] = $row;
                    } else {
                        $orderResultSet[$resultRow->getCatalogNumber()][$resultRow->{'get'.$identifierName}()] = $resultRow;
                    }
                }
            }
        }

        return $orderResultSet;
    }


    /**
     * @param array  $resultsSet
     * @return array
     */
    protected function orderResultSetByCatalogNumber($resultsSet)
    {
        $orderResultSet = [];
        if (count($resultsSet)) {
            foreach ($resultsSet as $resultRow) {
                if (!empty($resultRow)) {
                    if (is_array($resultRow)) {
                        $row = $resultRow[0];
                        $catalogNumber = $resultRow['catalognumber'];
                        $orderResultSet[$catalogNumber] = $row;
                    } else {
                        $orderResultSet[$resultRow->getCatalogNumber()] = $resultRow;
                    }
                }
            }
        }

        return $orderResultSet;
    }

    /**
     * @param Collection   $collection
     * @param QueryBuilder $qb
     * @param array        $catalogNumbers
     * @param string       $alias
     */
    protected function setSpecimenCodesWhereClause(
        Collection $collection,
        QueryBuilder &$qb,
        $catalogNumbers,
        $alias = 's'
    ) {
        $qb->andWhere(sprintf('%s.institutioncode = :institutionCode', $alias))
            ->andWhere(sprintf('%s.collectioncode = :collectionCode', $alias))
            ->andWhere($qb->expr()->in(sprintf('%s.catalognumber', $alias), ':catalogNumbers'))
            ->setParameters([
                'institutionCode' => $collection->getInstitution()->getInstitutioncode(),
                'collectionCode' => $collection->getCollectioncode(),
                'catalogNumbers' => $catalogNumbers,
            ]);
    }

    /**
     * @param $specimenCodes
     * @return array
     */
    protected function splitSpecimenCodes($specimenCodes)
    {
        $catalogNumbers = [];
        list($institutionCode, $collectionCode,) = explode('#', current($specimenCodes));
        foreach ($specimenCodes as $specimenCode) {
            $temp = explode('#', $specimenCode);
            $catalogNumbers[] = end($temp);
        }

        return array($catalogNumbers, $institutionCode, $collectionCode);
    }

    /**
     * @param string $className
     * @param string $id
     * @return \Doctrine\ORM\Query
     */
    public function getQueryFindOneById($className, $id)
    {
        return $this->getQbFindOneById($className, $id)
            ->select('a')
            ->getQuery();
    }

    /**
     * @param string $className
     * @param string $id
     * @return QueryBuilder
     */
    private function getQbFindOneById($className, $id)
    {
        $rawId = $this->hasRawId($className);
        $identifierName = $this->getIdentifierName($className);

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from(self::ENTITY_PREFIX.ucfirst($className), 'a')
            ->where('a.'.$identifierName.' = :id')
            ->setParameter('id', $id, $rawId ? 'rawid' : null);

        return $qb;
    }

    /**
     * @param string $className
     * @param string $id
     * @param string $field
     * @return mixed
     */
    public function findOneFieldById($className, $id, $field)
    {
        return $this->getQbFindOneById($className, $id)
            ->select('a.'.$field)
            ->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array $datas
     * @return QueryBuilder
     */
    public function createUpdateQuery(array $datas)
    {
        $qb = $this->createQueryBuilder('a')->update();

        foreach ($datas as $field => $value) {
            $qb->set('a.'.$field, $qb->expr()->literal($value));
        }

        return $qb;
    }

    /**
     * @param $className
     * @return boolean
     */
    public function hasRawId($className)
    {
        $rawId = self::ENTITY_DESCR[strtolower($className)]['rawid'];

        return $rawId;
    }

    /**
     * @param $className
     * @return string
     */
    private function getIdentifierName($className)
    {
        $identifierName = self::ENTITY_DESCR[strtolower($className)]['identifier'];

        return $identifierName;
    }
}
