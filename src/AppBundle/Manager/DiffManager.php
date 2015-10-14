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

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }
    
    public function getAllDiff($institutionCode) 
    {
        $diffSpecimensId=$this->getDiff('Specimen', $institutionCode);
        $diffBibliographiesId=$this->getDiff('Bibliography', $institutionCode);
        $diffDeterminationsId=$this->getDiff('Determination', $institutionCode);
        $diffLocalisationsId=$this->getDiff('Localisation', $institutionCode);
        $diffRecoltesId=$this->getDiff('Recolte', $institutionCode);
        $diffStratigraphiesId=$this->getDiff('Stratigraphy', $institutionCode);
        $diffTaxonsId=$this->getDiff('Taxon', $institutionCode);
        return [
            'specimens'               => $diffSpecimensId,
            'bibliographies'          => $diffBibliographiesId,
            'determinations'        => $diffDeterminationsId,
            'localisations'             => $diffLocalisationsId,
            'recoltes'                    => $diffRecoltesId,
            'stratigraphies'          => $diffStratigraphiesId,
            'taxons'                      => $diffTaxonsId
        ];
    }
    
    public function getGenericDiffQuery()
    {
        
        $metadata = $this->em->getMetadataFactory()->getMetadataFor($this->fullClassName) ;

        $aliasR = 'r';
        $aliasI = 'i';
        $identifier = $metadata->getIdentifier()[0] ;
        $strQuery='SELECT ';
        $arrayFields = $this->formatFieldsName($metadata, $aliasR, $aliasI) ;
        $strFromClauseRecolnat = $this->getFromClause($aliasR, false);
        $strFromClauseDiff = $this->getFromClause($aliasI, true);
        $strUnionQuery = 'SELECT '.$this->selectRawToHex($identifier).' FROM ('.
                $strQuery.implode(', ',$arrayFields['recolnat']).sprintf($strFromClauseRecolnat, self::RECOLNAT_DB.'.'.$metadata->getTableName()).
                ' UNION '.
                $strQuery. implode(', ', $arrayFields['institution']).sprintf($strFromClauseDiff, self::RECOLNAT_DIFF_DB.'.'.$metadata->getTableName()).
                ') GROUP BY %s HAVING COUNT(%s) >1';
        
        return sprintf($strUnionQuery, $identifier, $identifier, $identifier) ;
    }
    
    private function selectRawToHex($identifier) {
        if (in_array($this->class, ['Collection', 'Institution', 'Localisation', 'Stratigraphy'])) {
            return '%s';
        }
        return 'RAWTOHEX(%s) as '.$identifier ;
    }
    
    private function formatFieldsName($metadata, $aliasR, $aliasI)
    {
        $fieldsName = array_keys($metadata->reflFields) ;
        foreach ($fieldsName as $key=>$fieldName) {
            if (strtolower($metadata->getTypeOfField($fieldName)) === 'text') {
                $arrayFieldsTypeR[$key] =sprintf('dbms_lob.substr( %s.%s, %d, 1 )', $aliasR, $fieldName, self::LENGTH_TEXT) ;
                $arrayFieldsTypeD[$key] =sprintf('dbms_lob.substr( %s.%s, %d, 1 )', $aliasI, $fieldName, self::LENGTH_TEXT) ;
            }
            else {
                $arrayFieldsTypeR[$key] = sprintf('%s.%s',$aliasR, $fieldName) ;
                $arrayFieldsTypeD[$key] = sprintf('%s.%s',$aliasI, $fieldName) ;
            }
            switch (strtolower($fieldName)) {
                // Specimen
                case 'datepublication':
                    $arrayFieldsTypeR[$key] = sprintf('%s.%s',$aliasR, 'date_Publication') ;
                    $arrayFieldsTypeD[$key] = sprintf('%s.%s',$aliasI, 'date_Publication') ;
                    break;
                // Specimen
                case 'multimediaid' :
                    unset($arrayFieldsTypeR[$key]);
                    unset($arrayFieldsTypeD[$key]);
                    break;
                // Stratigraphy
                case 'group':
                    $arrayFieldsTypeR[$key] = sprintf('%s.%s',$aliasR, 'group_') ;
                    $arrayFieldsTypeD[$key] = sprintf('%s.%s',$aliasI, 'group_') ;
                    break;
                // Taxon
                case 'class' :
                    $arrayFieldsTypeR[$key] = sprintf('%s.%s',$aliasR, 'class_') ;
                    $arrayFieldsTypeD[$key] = sprintf('%s.%s',$aliasI, 'class_') ;
                    break;
                // Taxon
                case 'order' :
                    $arrayFieldsTypeR[$key] = sprintf('%s.%s',$aliasR, 'order_') ;
                    $arrayFieldsTypeD[$key] = sprintf('%s.%s',$aliasI, 'order_') ;
                    break;
            }
        }
        return ['recolnat'=>$arrayFieldsTypeR, 'institution'=>$arrayFieldsTypeD] ;
    }
    public function getFromClause($alias, $diff=false)
    { 
        $metadataSpecimen = $this->em->getMetadataFactory()->getMetadataFor(self::SPECIMEN_CLASSNAME) ;
        $specimenTableName = ($diff ? self::RECOLNAT_DIFF_DB : self::RECOLNAT_DB).'.'.$metadataSpecimen->getTableName() ;
        
        switch ($this->fullClassName) {
            case 'AppBundle:Specimen' : 
                return ' FROM %s '.$alias.' WHERE INSTITUTIONCODE = :institutionCode' ;
                //return ' FROM %s '.$alias.' '.$this->getSqlClauseJoinInstitutions($alias) ;
            case 'AppBundle:Bibliography' :
            case 'AppBundle:Determination' :
                return ' FROM %s '.$alias.' INNER JOIN '.$specimenTableName.' s ON s.OCCURRENCEID = '
                    .$alias. '.OCCURRENCEID AND '
                    . 's.INSTITUTIONCODE = :institutionCode' ;
                /*return ' FROM %s '.$alias.' INNER JOIN '.$specimenTableName.' s ON s.OCCURRENCEID = '
                    .$alias. '.OCCURRENCEID '.$this->getSqlClauseJoinInstitutions('s') ;*/
            case 'AppBundle:Localisation' :
                $metadataRecolte = $this->em->getMetadataFactory()->getMetadataFor(self::RECOLTE_CLASSNAME) ;
                $recolteTableName = ($diff ? self::RECOLNAT_DIFF_DB : self::RECOLNAT_DB).'.'.$metadataRecolte->getTableName() ;
                return ' FROM %s '.$alias
                    . ' INNER JOIN '.$recolteTableName.' ON '.$recolteTableName.'.LOCATIONID = '.$alias.'.LOCATIONID'
                    . ' INNER JOIN '.$specimenTableName.' s ON s.EVENTID = '
                    .$recolteTableName. '.EVENTID AND '
                    . 's.INSTITUTIONCODE = :institutionCode' ;
                /*return ' FROM %s '.$alias
                    . ' INNER JOIN '.$recolteTableName.' ON '.$recolteTableName.'.LOCATIONID = '.$alias.'.LOCATIONID'
                    . ' INNER JOIN '.$specimenTableName.' s ON s.EVENTID = '
                    .$recolteTableName. '.EVENTID '.$this->getSqlClauseJoinInstitutions('s') ;*/
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
    private function getSqlClauseJoinInstitutions($alias)
    {
        return 'INNER JOIN Collections c ON '.$alias.'.COLLECTIONID = c.COLLECTIONID 
                    INNER JOIN Institutions i ON c.INSTITUTIONID = i.INSTITUTIONID AND 
                    i.INSTITUTIONCODE = :institutionCode' ;
    }
    public function getDiff($className, $institutionCode) 
    {
        $this->class = ucfirst(strtolower($className)) ;
        $this->fullClassName = 'AppBundle:'.$this->class;
        $sqlDiff = $this->getGenericDiffQuery() ;
        
        $this->em->getConnection()->setFetchMode(\PDO::FETCH_NUM);
        $results = $this->em->getConnection()
                ->executeQuery($sqlDiff, array('institutionCode'=>$institutionCode))
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
