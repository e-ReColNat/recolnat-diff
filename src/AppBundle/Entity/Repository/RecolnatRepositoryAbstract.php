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
     * @param array $ids
     */
    abstract public function findOneById($id);
    
    public static function getExprConcatSpecimenCode(\Doctrine\ORM\QueryBuilder $qb, $alias='s') 
    {
        $concatFields = array(
            sprintf('%s.institutioncode', $alias),
            sprintf('%s.collectioncode', $alias),
            sprintf('%s.catalognumber', $alias),
        );
        return new Expr\Func('CONCAT', $concatFields);
       /* foreach ($concatFields as $field) {
            $qb->expr()->concat($concatFields[0], $concatFields[1], $concatFields[2]) ;
        }*/
        /*foreach ($concatFields as $field) {
            if (!isset($searchIn)) {
                $searchIn = $qb->expr()->concat($qb->expr()->literal(''), $field);
                continue;
            }

            $searchIn = $qb->expr()->concat(
                $searchIn,
                $qb->expr()->concat($qb->expr()->literal(''), $field)
            );
        }*/
        //return $searchIn;
    }
    
    protected function orderResultSetBySpecimenId($resultsSet, $identifierName)
    {
        $orderResultSet=[] ;
        if (count($resultsSet)>0) {
            foreach ($resultsSet as $resultRow) {
                $orderResultSet[$resultRow['specimenid']][$resultRow[0]->{'get'.$identifierName}()] = $resultRow[0] ;
            }
        }
        return $orderResultSet;
    }
}
