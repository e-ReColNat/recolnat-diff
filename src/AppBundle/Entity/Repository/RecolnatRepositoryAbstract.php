<?php
namespace AppBundle\Entity\Repository;

use Doctrine\ORM\Query\Expr;

/**
 * Description of RecolnatRepository
 *
 * @author tpateffoz
 */
abstract class RecolnatRepositoryAbstract extends \Doctrine\ORM\EntityRepository
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
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $alias
     * @return Expr\Func
     */
    public static function getExprConcatSpecimenCode($alias = 's')
    {
        $concatFields = array(
            sprintf('%s.institutioncode', $alias),
            sprintf('%s.collectioncode', $alias),
            sprintf('%s.catalognumber', $alias),
        );
        return new Expr\Func('CONCAT', $concatFields);
    }


    /**
     * @param array $resultsSet
     * @param string $identifierName
     * @return array
     */
    protected function orderResultSetBySpecimenCode($resultsSet, $identifierName)
    {
        $orderResultSet = [];
        if (count($resultsSet) > 0) {
            foreach ($resultsSet as $resultRow) {
                $orderResultSet[$resultRow['specimencode']][$resultRow[0]->{'get' . $identifierName}()] = $resultRow[0];
            }
        }
        return $orderResultSet;
    }
}
