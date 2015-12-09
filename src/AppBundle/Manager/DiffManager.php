<?php

namespace AppBundle\Manager ;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Manager\DiffStatsManager;
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
    
    /**
     *
     * @var DiffStatsManager
     */
    protected $statsManager ;
    
    protected $exportPath;
 
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

    public function __construct(ObjectManager $em, DiffStatsManager $statsManager, $exportPath)
    {
        $this->em = $em;
        $this->statsManager = $statsManager;
        $this->exportPath = $exportPath;
    }
    
    public function init($institutionCode, array $selectedClassesName = [], array $selectedSpecimensCode=[], array $choicesToRemove=[]) {
        $this->institutionCode = $institutionCode ;
        $filePath = $this->getFilePath();
        
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        if ($fs->exists($filePath)) {
            $fileContent=  json_decode(file_get_contents($filePath),true);
            $specimensCode = $fileContent['specimensCode'];
            $diffs = $fileContent['diffs'];
            $stats = $fileContent['stats'];
        }
        else {
            $diffs[$institutionCode] = $this->getAllDiff();
            $specimensCode = $this->getSpecimensCode($institutionCode);
            $diffStatsManager = $this->statsManager->init($diffs[$institutionCode]);
            $stats = $diffStatsManager->getStats();
            $responseJson = json_encode(
                    [
                    'specimensCode' => $specimensCode,
                    'stats' => $stats, 
                    'diffs' => $diffs
                    ]
                    , JSON_PRETTY_PRINT);
            $fs->dumpFile($filePath, $responseJson);
        }
        $stats = $this->filterResults($stats, array_filter($selectedClassesName), $selectedSpecimensCode, $choicesToRemove);
        return array(
            'specimensCode' => $specimensCode,
            'stats' => $stats, 
            'diffs' => $diffs) ;
    }
    
    /**
     * renvoie les résultats dont au moins une différence fait partie de $classesName
     * @param array $stats
     * @param array $classesName
     * @return array
     */
    public function filterByClassesName($stats, array $classesName=[]) {
        $returnStats=$stats;
        
        if (count($classesName)>0) {
            $returnStats['classes']=[] ;
            $returnStats['summary'] = [];
            foreach ($classesName as $className) {
                $className = ucfirst(strtolower($className));
                if (isset($stats['classes'][$className])) {
                    $returnStats['classes'][$className] = $stats['classes'][$className] ;
                }
            }
            foreach ($returnStats['classes'] as $className => $row) {
                foreach ($row as $specimenCode => $fields) {
                    if (isset($stats['summary'][$specimenCode])) {
                        $returnStats['summary'][$specimenCode] = $stats['summary'][$specimenCode] ;
                        // Rajout dans les classes si un specimen a des mofifications dans des class non sélectionnées
                        foreach (array_keys($returnStats['summary'][$specimenCode]) as $className) {
                            if (!isset($returnStats['classes'][$className][$specimenCode])) {
                                $returnStats['classes'][$className][$specimenCode] = $stats['classes'][$className][$specimenCode] ;
                            }
                        }
                    }
                }
            }
        }
        
        return $returnStats ;
    }
    
    /**
     * renvoie les résultats dont le specimenCode fait partie de $selectedSpecimensCode
     * @param array $stats
     * @param array $selectedSpecimensCode
     * @return array
     */
    public function filterBySpecimensCode($stats,array $selectedSpecimensCode=[]) {
        $returnStats=$stats;
        if (count($selectedSpecimensCode)>0) {
            // Remise du summary à zero
            $returnStats['summary']=[];
            $returnStats['classes']=$stats['classes'];
            foreach ($stats['classes'] as $className => $row) {
                foreach ($row as $specimenCode => $fields) {
                    if (in_array($specimenCode, $selectedSpecimensCode)) {
                        $returnStats['summary'][$specimenCode] = $stats['summary'][$specimenCode] ;
                    }
                    else {
                        unset($returnStats['classes'][$className][$specimenCode]);
                    }
                }
            }
        }
        return $returnStats;
    }
    
    /**
     * filtre les résultats dont les choix ont été complétement faits
     * @param type $stats
     * @param array $choicesToRemove
     * @return type
     */
    public function filterByChoicesDone($stats, array $choicesToRemove=[]) {
        $returnStats=$stats;
        if (count($choicesToRemove) >0) {
            $tempChoices=[] ;
            foreach ($choicesToRemove as $choice) {
                if (!isset($tempChoices[$choice['className']])) {
                    $tempChoices[$choice['className']]=[];
                }
                if (!isset($tempChoices[$choice['className']][$choice['specimenId']])) {
                    $tempChoices[$choice['className']][$choice['specimenId']]=0;
                }
                $tempChoices[$choice['className']][$choice['specimenId']]++;
            }
            foreach ($tempChoices as $className => $choiceSpecimenId) {
                foreach ($choiceSpecimenId as $specimenCode => $comptFieldChoice) {
                    if (isset($returnStats['classes'][$className]) && isset($returnStats['classes'][$className][$specimenCode])) {
                        $totalStatFields=0;
                        foreach ($returnStats['classes'][$className][$specimenCode] as $statsFields) {
                            $totalStatFields+=count($statsFields) ;
                        }
                        if ($totalStatFields == $comptFieldChoice) {
                            unset($returnStats['classes'][$className][$specimenCode]) ;
                            unset($returnStats['summary'][$specimenCode][$className]) ;
                            if (isset($returnStats['summary'][$specimenCode]) && count($returnStats['summary'][$specimenCode]) == 0) {
                                unset($returnStats['summary'][$specimenCode]);
                            }
                        }
                    }
                }
            }
        }
        return $returnStats ;
    }
    
    /**
     * filtre les résultats
     * @param array $stats
     * @param array $classesName
     * @param array $selectedSpecimensCode
     * @param array $choicesToRemove
     * @return array
     */
    public function filterResults($stats, array $classesName=[], array $selectedSpecimensCode=[], array $choicesToRemove=[]) {
        $returnStats = $this->filterByClassesName($stats, $classesName) ;
        $returnStats = $this->filterBySpecimensCode($returnStats, $selectedSpecimensCode);
        $returnStats = $this->filterByChoicesDone($returnStats, $choicesToRemove);
        return $returnStats;
    }
    
    public function getFilePath() {
         return realpath($this->exportPath) . '/' . $this->institutionCode . '.json';
    }
    private function getAllDiff() 
    {
        foreach ($this->entitiesName as $entityName) {
            $results[$entityName] = $this->getDiff($entityName);
        }
        return $results;
    }

    /**
     * Renvoie un tableau des codes des specimens ayant une différence
     * @param string $institutionCode
     * @return array
     */
    public function getSpecimensCode($institutionCode) 
    {
        $returnSpecimensCode=[];
        $results = $this->getAllDiff($institutionCode) ;
        if (count($results)>0) {
            foreach ($results as $specimensCode) {
                foreach ($specimensCode as $specimenCode) {
                    $returnSpecimensCode[] = $specimenCode ;
                }
            }
        }
        return array_values(array_unique($returnSpecimensCode));
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
        $this->fullClassName = $this->getFullClassName($this->class) ;
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
    
    private function getFullClassName($class) {
        return 'AppBundle:'.$class;
    }
    
    public function generateDiff($compt) {
        $randomClassName= $this->getFullClassName($this->entitiesName[array_rand($this->entitiesName, 1)]) ;
        //$randomClassName= $this->getFullClassName("Localisation") ;
        $metadata = $this->em->getMetadataFactory()->getMetadataFor($randomClassName) ;
        $repository = $this->em->getRepository($randomClassName);
        $identifier = $metadata->getIdentifierFieldNames() [0];

        $entity = $repository->createQueryBuilder('e')
                ->orderBy('RAND()')
                ->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
        $fields = $metadata->getFieldNames() ;

        var_dump($randomClassName, $entity->{'get'.$identifier}()) ;
        //var_dump($fields);
        // On enleve le champ de l'indentifiant
        unset($fields[array_search($identifier, $fields)]) ;
        unset($fields[array_search('dwcaid', $fields)]) ;
        unset($fields[array_search('hasmedia', $fields)]) ;
        unset($fields[array_search('modified', $fields)]) ;
        unset($fields[array_search('catalognumber', $fields)]) ;
        unset($fields[array_search('institutioncode', $fields)]) ;
        unset($fields[array_search('sourcefileid', $fields)]) ;
        
        shuffle($fields) ;
        $randomFields = array_slice($fields, 0, $compt) ;
        //var_dump($randomFields);
        $fieldMappings = $this->em->getClassMetadata($randomClassName)->fieldMappings ;
        foreach ($randomFields as $fieldName) {
            var_dump($fieldMappings[$fieldName]);
            $setter = 'set'.$fieldName ;
            if ($fieldName[strlen($fieldName)-1] == '_') {
                $setter ='set'.substr($fieldName, 0, -1) ;
            }
            $entity->{$setter}($this->getFakeData($metadata->getTypeOfField($fieldName), $fieldMappings[$fieldName]['length'])) ;
            
        }
        $this->em->flush($entity) ;
        
        
        return $randomFields ;
    }
    
    private function getFakeData($type, $length = null) 
    {
        switch ($type) {
            case 'string' : 
            case 'text' : 
                $arrayString=['lorem', 'lorem ipsum', 'blabla', 'text sample'] ;
                $returnString = $arrayString[array_rand($arrayString)] ;
                if (!is_null($length)) {
                    $returnString = substr($returnString, 0, $length);
                }
                return $returnString;
            case 'integer' : 
                if ($length === null) {$length = 2;}
                $arrayInt = range(10,  (10 * $length)) ;
                return $arrayInt[array_rand($arrayInt)];
            case 'float' : 
                if ($length === null) {$length = 2;}
                $arrayFloat = range(10, (10 * $length), 0.1) ;
                return $arrayFloat[array_rand($arrayFloat)];
            case 'datetime' : 
                $arrayDate=['1976-11-08', '2005-12-25', '1953-01-31', '2006-12-13'] ;
                return new \DateTime($arrayDate[array_rand($arrayDate)]);
            case 'boolean' : 
                return array_rand([true, false]);
        }
    }
}
