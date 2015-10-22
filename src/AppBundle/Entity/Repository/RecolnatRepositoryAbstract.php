<?php
namespace AppBundle\Entity\Repository;
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
    
    public static function getExprConcatSpecimenCode(\Doctrine\ORM\QueryBuilder $qb, $alias='s') 
    {
        $concatFields = array(
            sprintf('%s.institutioncode', $alias),
            sprintf('%s.collectioncode', $alias),
            sprintf('%s.catalognumber', $alias),
        );
        foreach ($concatFields as $field) {
            if (!isset($searchIn)) {
                $searchIn = $qb->expr()->concat($qb->expr()->literal(''), $field);
                continue;
            }

            $searchIn = $qb->expr()->concat(
                $searchIn,
                $qb->expr()->concat($qb->expr()->literal(''), $field)
            );
        }
        return $searchIn;
    }
    
    protected function orderResultSetBySpecimenId($resultsSet)
    {
        $orderResultSet=[] ;
        if (count($resultsSet)>0) {
            foreach ($resultsSet as $resultRow) {
                $orderResultSet[$resultRow['specimenid']] = $resultRow[0] ;
            }
        }
        return $orderResultSet;
    }
}
