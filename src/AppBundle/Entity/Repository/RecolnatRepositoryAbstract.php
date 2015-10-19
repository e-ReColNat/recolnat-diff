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
    
    public function getExprConcatSpecimenCode(\Doctrine\ORM\QueryBuilder $qb) 
    {
        $concatFields = array(
            's.institutioncode',
            's.collectioncode',
            's.catalognumber',
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
