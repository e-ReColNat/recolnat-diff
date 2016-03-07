<?php
namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * Description of RecolnatRepository
 *
 * @author tpateffoz
 */
abstract class RecolnatRepositoryAbstract extends EntityRepository
{
    /**
     *
     * @param array $specimenCodes
     * @return array
     */
    abstract public function findBySpecimenCodes($specimenCodes);

    abstract public function findBySpecimenCodeUnordered($specimenCodes);

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
        if (count($resultsSet) > 0) {
            foreach ($resultsSet as $resultRow) {
                $orderResultSet[$resultRow['specimencode']][$resultRow[0]->{'get'.$identifierName}()] = $resultRow[0];
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

        $catalogNumbers = [];
        list($institutionCode, $collectionCode,) = explode('#', current($specimenCodes));
        foreach ($specimenCodes as $specimenCode) {
            $temp = explode('#', $specimenCode);
            $catalogNumbers[] = end($temp);
        }

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
}
