<?php
namespace AppBundle\Entity\Repository;

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

    /**
     *
     * @param array $specimenCodes
     * @param       $hydratationMode int
     * @return array
     */
    abstract public function findBySpecimenCodes($specimenCodes, $hydratationMode = AbstractQuery::HYDRATE_ARRAY);

    abstract public function findBySpecimenCodeUnordered($specimenCodes);

    /**
     * @param Collection $collection
     * @return \Doctrine\ORM\QueryBuilder
     */
    abstract public function getQueryBuilderFindByCollection(Collection $collection);

    /**
     *
     * @param array $ids
     * @return array
     */
    abstract public function findById($ids);

    /**
     *
     * @param array $id
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

    public static function getExprConcatSpecimenCode($alias = 's')
    {
        $concatFields = array(
            sprintf('%s.institutioncode', $alias),
            "'#'",
            sprintf('%s.collectioncode', $alias),
            "'#'",
            sprintf('%s.catalognumber', $alias),
        );
        return new Expr\Func('CONCAT', $concatFields);
    }


    /**
     * @param array  $resultsSet
     * @param string $identifierName
     * @return array
     */
    protected function orderResultSetBySpecimenCode($resultsSet, $identifierName)
    {
        $orderResultSet = [];
        if (count($resultsSet)) {
            foreach ($resultsSet as $resultRow) {
                if (!empty($resultRow)) {
                    if (is_array($resultRow[0])) {
                        $specimenCode = $resultRow['specimencode'];
                        $orderResultSet[$specimenCode][$resultRow[0][$identifierName]] = $resultRow[0];
                    } else {
                        $orderResultSet[$resultRow['specimencode']][$resultRow[0]->{'get'.$identifierName}()] = $resultRow[0];
                    }
                }
            }
        }
        return $orderResultSet;
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $specimenCodes
     * @param string       $alias
     */
    protected function setSpecimenCodesWhereClause(QueryBuilder &$qb, $specimenCodes, $alias = 's')
    {

        list($catalogNumbers, $institutionCode, $collectionCode) = $this->splitSpecimenCodes($specimenCodes);

        $qb->andWhere(sprintf('%s.institutioncode = :institutionCode', $alias))
            ->andWhere(sprintf('%s.collectioncode = :collectionCode', $alias))
            ->andWhere($qb->expr()->in(sprintf('%s.catalognumber', $alias), ':catalogNumbers'))
            ->setParameters([
                'institutionCode' => $institutionCode,
                'collectionCode' => $collectionCode,
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
     * @param $id
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
