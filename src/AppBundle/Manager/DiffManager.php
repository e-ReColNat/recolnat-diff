<?php

namespace AppBundle\Manager ;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
/**
 * Description of DiffManager
 *
 * @author tpateffoz
 */
class DiffManager
{
    /**
     * Holds the Doctrine entity manager for database interaction
     * @var EntityManager 
     */
    protected $em;
 
    const LENGTH_TEXT = 4000;
    const RECOLNAT_DB = 'RECOLNAT';
    const RECOLNAT_DIFF_DB = 'RECOLNAT_DIFF';
    const SPECIMEN_CLASSNAME =  'AppBundle:Specimen';
    const RECOLTE_CLASSNAME =  'AppBundle:Recolte';
    const DETERMINATION_CLASSNAME =  'AppBundle:Determination';
    protected $class;
    protected $fullClassName;
    protected $institutionCode;
    protected $entitiesName=[
            'Specimen',     
            'Bibliography',
            'Determination',
            'Localisation',
            'Recolte',
            'Stratigraphy',
            'Taxon'
        ];

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }
    
    public function getAllDiff($institutionCode) 
    {
        $this->institutionCode = $institutionCode ;
        foreach ($this->entitiesName as $entityName) {
            $results[$entityName] = $this->getDiff($entityName);
        }
        return $results;
    }
    
    /**
     * Renvoie un tableau des codes des specimens ayant une différence
     * @param array $results
     * @return array
     */
    public static function getSpecimensCode($results) 
    {
        foreach ($results as $specimensCode) {
            foreach ($specimensCode as $specimenCode) {
                $returnSpecimensCode[] = $specimenCode ;
            }
        }
        return array_unique($returnSpecimensCode);
    }
    
    public function getGenericDiffQuery()
    {
        /* @var $metada \Doctrine\ORM\Mapping\ClassMetadata */
        $metadata = $this->em->getMetadataFactory()->getMetadataFor($this->fullClassName) ;

        $aliasR = 'r';
        $aliasI = 'i';

        $identifier = 'specimenId' ;
        $strQuery='SELECT '.$identifier.' FROM (';
        //$strQuery='';
        $arrayFields = $this->formatFieldsName($metadata, $aliasR, $aliasI) ;
        $strFromClauseRecolnat = $this->getFromClause($aliasR, false);
        $strFromClauseDiff = $this->getFromClause($aliasI, true);
        $strUnionQuery = $strQuery.
                'SELECT '.
                implode(', ',$arrayFields['recolnat']).
                sprintf($strFromClauseRecolnat, self::RECOLNAT_DB.'.'.$metadata->getTableName()).
                ' MINUS '.
                'SELECT '.
                implode(', ', $arrayFields['institution']).
                sprintf($strFromClauseDiff, self::RECOLNAT_DIFF_DB.'.'.$metadata->getTableName())
                ;
        //$sqlGroupByCount = ' ) GROUP BY %s HAVING COUNT(*) >1' ;
        $sqlGroupByCount = ')' ;
        return sprintf($strUnionQuery.$sqlGroupByCount, $identifier, $identifier, $identifier) ;
    }

    private function getSpecimenUniqueIdClause($alias)
    {
        return sprintf(' %s.institutioncode||%s.collectioncode||%s.catalognumber as specimenId ', $alias, $alias, $alias);
    }
    private function formatFieldsName(\Doctrine\ORM\Mapping\ClassMetadata $metadata, $aliasR, $aliasI)
    {
        $identifier = key(array_flip($metadata->getIdentifier())) ;
        $fields = array_flip($metadata->getFieldNames() );
        unset($fields[$identifier]) ;
        $fields = array_flip($fields) ;

        $fieldsName = $fields ;
        $arrayFieldsTypeR=[] ;
        $arrayFieldsTypeI=[] ;
        foreach ($fieldsName as $key=>$fieldName) {
            if (strtolower($metadata->getTypeOfField($fieldName)) === 'text') {
                $arrayFieldsTypeR[$key] =sprintf('dbms_lob.substr( %s.%s, %d, 1 )', $aliasR, $fieldName, self::LENGTH_TEXT) ;
                $arrayFieldsTypeI[$key] =sprintf('dbms_lob.substr( %s.%s, %d, 1 )', $aliasI, $fieldName, self::LENGTH_TEXT) ;
            }
            else {
                $arrayFieldsTypeR[$key] = sprintf('%s.%s',$aliasR, $fieldName) ;
                $arrayFieldsTypeI[$key] = sprintf('%s.%s',$aliasI, $fieldName) ;
            }
            switch (strtolower($fieldName)) {
                // Specimen
                case 'datepublication':
                    $arrayFieldsTypeR[$key] = sprintf('%s.%s',$aliasR, 'date_Publication') ;
                    $arrayFieldsTypeI[$key] = sprintf('%s.%s',$aliasI, 'date_Publication') ;
                    break;
                // Specimen
                case 'multimediaid' :
                    unset($arrayFieldsTypeR[$key]);
                    unset($arrayFieldsTypeI[$key]);
                    break;
                // Stratigraphy
                case 'group':
                    $arrayFieldsTypeR[$key] = sprintf('%s.%s',$aliasR, 'group_') ;
                    $arrayFieldsTypeI[$key] = sprintf('%s.%s',$aliasI, 'group_') ;
                    break;
                // Taxon
                case 'class' :
                    $arrayFieldsTypeR[$key] = sprintf('%s.%s',$aliasR, 'class_') ;
                    $arrayFieldsTypeI[$key] = sprintf('%s.%s',$aliasI, 'class_') ;
                    break;
                // Taxon
                case 'order' :
                    $arrayFieldsTypeR[$key] = sprintf('%s.%s',$aliasR, 'order_') ;
                    $arrayFieldsTypeI[$key] = sprintf('%s.%s',$aliasI, 'order_') ;
                    break;
                case 'determinations' : 
                    unset($arrayFieldsTypeR[$key]);
                    unset($arrayFieldsTypeI[$key]);
                    break;
            }
        }
        $aliasSpecimenR = 's' ;
        $aliasSpecimenI = 's' ;
        if ($this->class == 'Specimen') {
            $aliasSpecimenR = $aliasR ;
            $aliasSpecimenI = $aliasI ;
        }
        $arrayFieldsTypeR[] = $this->getSpecimenUniqueIdClause($aliasSpecimenR) ;
        $arrayFieldsTypeI[] = $this->getSpecimenUniqueIdClause($aliasSpecimenI) ;
        return ['recolnat'=>$arrayFieldsTypeR, 'institution'=>$arrayFieldsTypeI] ;
    }
    public function getFromClause($alias, $diff=false)
    { 
        $metadataSpecimen = $this->em->getMetadataFactory()->getMetadataFor(self::SPECIMEN_CLASSNAME) ;
        $specimenTableName = ($diff ? self::RECOLNAT_DIFF_DB : self::RECOLNAT_DB).'.'.$metadataSpecimen->getTableName() ;
        
        switch ($this->fullClassName) {
            case 'AppBundle:Specimen' : 
                return ' FROM %s '.$alias .' WHERE '.$alias.'.INSTITUTIONCODE = :institutionCode' ;
            case 'AppBundle:Bibliography' :
            case 'AppBundle:Determination' :
                return ' FROM %s '.$alias.' INNER JOIN '.$specimenTableName.' s ON s.OCCURRENCEID = '
                    .$alias. '.OCCURRENCEID AND '
                    . 's.INSTITUTIONCODE = :institutionCode' ;
            case 'AppBundle:Localisation' :
                $metadataRecolte = $this->em->getMetadataFactory()->getMetadataFor(self::RECOLTE_CLASSNAME) ;
                $recolteTableName = ($diff ? self::RECOLNAT_DIFF_DB : self::RECOLNAT_DB).'.'.$metadataRecolte->getTableName() ;
                return ' FROM %s '.$alias
                    . ' INNER JOIN '.$recolteTableName.' ON '.$recolteTableName.'.LOCATIONID = '.$alias.'.LOCATIONID'
                    . ' INNER JOIN '.$specimenTableName.' s ON s.EVENTID = '
                    .$recolteTableName. '.EVENTID AND '
                    . 's.INSTITUTIONCODE = :institutionCode' ;
            case 'AppBundle:Recolte' :
                return ' FROM %s '.$alias.' INNER JOIN '.$specimenTableName.' s ON s.EVENTID = '
                    .$alias. '.EVENTID AND '
                    . 's.INSTITUTIONCODE = :institutionCode' ;
            case 'AppBundle:Stratigraphy' :
                return ' FROM %s '.$alias.' INNER JOIN '.$specimenTableName.' s ON s.GEOLOGICALCONTEXTID = '
                    .$alias. '.GEOLOGICALCONTEXTID AND '
                    . 's.INSTITUTIONCODE = :institutionCode' ;
            case 'AppBundle:Taxon' :
                $metadataDetermination = $this->em->getMetadataFactory()->getMetadataFor(self::DETERMINATION_CLASSNAME) ;
                $determinationTableName = ($diff ? self::RECOLNAT_DIFF_DB : self::RECOLNAT_DB).'.'.$metadataDetermination->getTableName() ;
                return ' FROM %s '.$alias
                    . ' INNER JOIN '.$determinationTableName.' ON '.$determinationTableName.'.TAXONID = '.$alias.'.TAXONID'
                    . ' INNER JOIN '.$specimenTableName.' s ON s.OCCURRENCEID = '
                    .$determinationTableName. '.OCCURRENCEID AND '
                    . 's.INSTITUTIONCODE = :institutionCode' ;
        }
    }

    public function getDiff($className) 
    {
        $this->class = ucfirst(strtolower($className)) ;
        $this->fullClassName = 'AppBundle:'.$this->class;
        $sqlDiff = $this->getGenericDiffQuery() ;
        
        $this->em->getConnection()->setFetchMode(\PDO::FETCH_NUM);
        $results = $this->em->getConnection()
                ->executeQuery($sqlDiff, array('institutionCode'=>$this->institutionCode))
                ->fetchAll() ;
        
        $ids = array();
        if (count($results) >0) {
            foreach($results as $item) {
              $ids[] = $item[0];
            }
        }
        return $ids;
    }
}
