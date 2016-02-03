<?php

namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;

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

    /** @var DiffStatsManager */
    protected $statsManager;

    protected $exportPath;

    const LENGTH_TEXT = 4000;
    const RECOLNAT_DB = 'RECOLNAT';
    const RECOLNAT_DIFF_DB = 'RECOLNAT_DIFF';
    const SPECIMEN_CLASSNAME = 'AppBundle:Specimen';
    const RECOLTE_CLASSNAME = 'AppBundle:Recolte';
    const DETERMINATION_CLASSNAME = 'AppBundle:Determination';
    protected $class;
    protected $fullClassName;
    protected $institutionCode;
    protected $collectionCode;
    protected $entitiesName = [
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

    public function init($institutionCode, $collectionCode)
    {
        $this->institutionCode = $institutionCode;
        $this->collectionCode = $collectionCode;
        $diffs = $this->getAllDiff();
        $diffStatsManager = $this->statsManager->init($diffs);
        $data = array_merge($diffStatsManager->getDiffs(),
            ['stats' => $diffStatsManager->getAllStats(), 'lonesomeRecords' => $diffStatsManager->getLonesomeRecords()]);
        return $data;
    }

    public function getFilePath()
    {
        return realpath($this->exportPath) . '/' . $this->institutionCode . '.json';
    }

    private function getAllDiff()
    {
        $results = [];
        foreach ($this->entitiesName as $entityName) {
            $results[$entityName] = $this->getDiff($entityName);
        }
        return $results;
    }

    private function getGenericDiffQuery(
        $db1=['name'=>self::RECOLNAT_DB, 'alias'=>'r'],
        $db2=['name'=>self::RECOLNAT_DIFF_DB, 'alias'=> 'i'])
    {
        /* @var $metadata \Doctrine\ORM\Mapping\ClassMetadata */
        $metadata = $this->em->getMetadataFactory()->getMetadataFor($this->fullClassName);

        $aliasDb1 = $db1['alias'];
        $aliasDb2 = $db2['alias'];

        $identifier = 'specimenCode';
        $strQuery = 'SELECT ' . $identifier . ' FROM (';
        $arrayFields = $this->formatFieldsName($metadata, $aliasDb1, $aliasDb2);
        $strFromClauseDb1 = $this->getFromClause($aliasDb1, false);
        $strFromClauseDb2 = $this->getFromClause($aliasDb2, true);
        $strUnionQuery = $strQuery .
            'SELECT ' .
            implode(', ', $arrayFields['db1']) .
            sprintf($strFromClauseDb1, $db1['name'] . '.' . $metadata->getTableName()) .
            ' MINUS ' .
            'SELECT ' .
            implode(', ', $arrayFields['db2']) .
            sprintf($strFromClauseDb2, $db2['name'] . '.' . $metadata->getTableName());
        $sqlGroupByCount = ')';
        return sprintf($strUnionQuery . $sqlGroupByCount, $identifier, $identifier, $identifier);
    }

    private function getSpecimenUniqueIdClause($alias)
    {
        return sprintf(' %s.institutioncode||%s.collectioncode||%s.catalognumber as specimenCode ', $alias, $alias,
            $alias);
    }

    /**
     * @param ClassMetadata $metadata
     * @param string $aliasDb1
     * @param string $aliasDb2
     * @return array
     */
    private function formatFieldsName(ClassMetadata $metadata, $aliasDb1, $aliasDb2)
    {
        $identifier = key(array_flip($metadata->getIdentifier()));
        $fields = array_flip($metadata->getFieldNames());
        unset($fields[$identifier]);
        $fields = array_flip($fields);

        $fieldsName = $fields;
        $arrayFieldsTypeDb1 = [];
        $arrayFieldsTypeDb2 = [];
        foreach ($fieldsName as $key => $fieldName) {
            if (strtolower($metadata->getTypeOfField($fieldName)) === 'text') {
                $arrayFieldsTypeDb1[$key] = sprintf('dbms_lob.substr( %s.%s, %d, 1 )', $aliasDb1, $fieldName,
                    self::LENGTH_TEXT);
                $arrayFieldsTypeDb2[$key] = sprintf('dbms_lob.substr( %s.%s, %d, 1 )', $aliasDb2, $fieldName,
                    self::LENGTH_TEXT);
            } else {
                $arrayFieldsTypeDb1[$key] = sprintf('%s.%s', $aliasDb1, $fieldName);
                $arrayFieldsTypeDb2[$key] = sprintf('%s.%s', $aliasDb2, $fieldName);
            }
            switch (strtolower($fieldName)) {
                // Specimen
                case 'datepublication':
                    $arrayFieldsTypeDb1[$key] = sprintf('%s.%s', $aliasDb1, 'date_Publication');
                    $arrayFieldsTypeDb2[$key] = sprintf('%s.%s', $aliasDb2, 'date_Publication');
                    break;
                // Specimen
                case 'multimediaid':
                    unset($arrayFieldsTypeDb1[$key]);
                    unset($arrayFieldsTypeDb2[$key]);
                    break;
                // Stratigraphy
                case 'group':
                    $arrayFieldsTypeDb1[$key] = sprintf('%s.%s', $aliasDb1, 'group_');
                    $arrayFieldsTypeDb2[$key] = sprintf('%s.%s', $aliasDb2, 'group_');
                    break;
                // Taxon
                case 'class':
                    $arrayFieldsTypeDb1[$key] = sprintf('%s.%s', $aliasDb1, 'class_');
                    $arrayFieldsTypeDb2[$key] = sprintf('%s.%s', $aliasDb2, 'class_');
                    break;
                // Taxon
                case 'order':
                    $arrayFieldsTypeDb1[$key] = sprintf('%s.%s', $aliasDb1, 'order_');
                    $arrayFieldsTypeDb2[$key] = sprintf('%s.%s', $aliasDb2, 'order_');
                    break;
                case 'determinations':
                case 'sourcefileid':
                case 'hascoordinates':
                case 'dwcataxonid':
                case 'dwcaid':
                case 'hasmedia':
                    unset($arrayFieldsTypeDb1[$key]);
                    unset($arrayFieldsTypeDb2[$key]);
                    break;
            }
        }
        $aliasSpecimenDb1 = 's';
        $aliasSpecimenDb2 = 's';
        if ($this->class == 'Specimen') {
            $aliasSpecimenDb1 = $aliasDb1;
            $aliasSpecimenDb2 = $aliasDb2;
        }
        $arrayFieldsTypeDb1[] = $this->getSpecimenUniqueIdClause($aliasSpecimenDb1);
        $arrayFieldsTypeDb2[] = $this->getSpecimenUniqueIdClause($aliasSpecimenDb2);
        return ['db1' => $arrayFieldsTypeDb1, 'db2' => $arrayFieldsTypeDb2];
    }

    /**
     * @param string
     * @param bool $institution
     * @return string
     */
    private function getFromClause($alias, $institution = false)
    {
        $metadataSpecimen = $this->em->getMetadataFactory()->getMetadataFor(self::SPECIMEN_CLASSNAME);
        $specimenTableName = ($institution ? self::RECOLNAT_DIFF_DB : self::RECOLNAT_DB) . '.' . $metadataSpecimen->getTableName();

        $fromClause='';
        switch ($this->fullClassName) {
            case 'AppBundle:Specimen':
                $fromClause = ' FROM %s ' . $alias . ' WHERE ' . $alias . '.INSTITUTIONCODE = :institutionCode AND '
                . $alias . '.COLLECTIONCODE = :collectionCode ';
                break;
            case 'AppBundle:Bibliography':
            case 'AppBundle:Determination':
                $fromClause = ' FROM %s ' . $alias . ' INNER JOIN ' . $specimenTableName . ' s ON s.OCCURRENCEID = '
                . $alias . '.OCCURRENCEID AND '
                . 's.INSTITUTIONCODE = :institutionCode AND '
                . 's.COLLECTIONCODE = :collectionCode ';
                break;
            case 'AppBundle:Localisation':
                $metadataRecolte = $this->em->getMetadataFactory()->getMetadataFor(self::RECOLTE_CLASSNAME);
                $recolteTableName = ($institution ? self::RECOLNAT_DIFF_DB : self::RECOLNAT_DB) . '.' . $metadataRecolte->getTableName();
                $fromClause = ' FROM %s ' . $alias
                . ' INNER JOIN ' . $recolteTableName . ' ON ' . $recolteTableName . '.LOCATIONID = ' . $alias . '.LOCATIONID'
                . ' INNER JOIN ' . $specimenTableName . ' s ON s.EVENTID = '
                . $recolteTableName . '.EVENTID AND '
                . 's.INSTITUTIONCODE = :institutionCode AND '
                . 's.COLLECTIONCODE = :collectionCode ';
                break;
            case 'AppBundle:Recolte':
                $fromClause = ' FROM %s ' . $alias . ' INNER JOIN ' . $specimenTableName . ' s ON s.EVENTID = '
                . $alias . '.EVENTID AND '
                . 's.INSTITUTIONCODE = :institutionCode AND '
                . 's.COLLECTIONCODE = :collectionCode ';
                break;
            case 'AppBundle:Stratigraphy':
                $fromClause = ' FROM %s ' . $alias . ' INNER JOIN ' . $specimenTableName . ' s ON s.GEOLOGICALCONTEXTID = '
                . $alias . '.GEOLOGICALCONTEXTID AND '
                . 's.INSTITUTIONCODE = :institutionCode AND '
                . 's.COLLECTIONCODE = :collectionCode ';
                break;
            case 'AppBundle:Taxon':
                $metadataDetermination = $this->em->getMetadataFactory()->getMetadataFor(self::DETERMINATION_CLASSNAME);
                $determinationTableName = ($institution ? self::RECOLNAT_DIFF_DB : self::RECOLNAT_DB) . '.' . $metadataDetermination->getTableName();
                $fromClause = ' FROM %s ' . $alias
                . ' INNER JOIN ' . $determinationTableName . ' ON ' . $determinationTableName . '.TAXONID = ' . $alias . '.TAXONID'
                . ' INNER JOIN ' . $specimenTableName . ' s ON s.OCCURRENCEID = '
                . $determinationTableName . '.OCCURRENCEID AND '
                . 's.INSTITUTIONCODE = :institutionCode AND '
                . 's.COLLECTIONCODE = :collectionCode ';
                break;
        }
        return $fromClause ;
    }

    private function searchNewRecords()
    {

    }

    /**
     * @param string $className
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getDiff($className)
    {
        $this->class = ucfirst(strtolower($className));
        $this->fullClassName = $this->getFullClassName($this->class);
        $db1=['name'=>self::RECOLNAT_DB, 'alias'=>'r'];
        $db2=['name'=>self::RECOLNAT_DIFF_DB, 'alias'=> 'i'];
        $sqlDiff1 = $this->getGenericDiffQuery($db1, $db2);
        $sqlDiff2 = $this->getGenericDiffQuery($db2, $db1);

        $this->em->getConnection()->setFetchMode(\PDO::FETCH_COLUMN, 0);
        $results1 = $this->em->getConnection()
            ->executeQuery($sqlDiff1,
                array('institutionCode' => $this->institutionCode, 'collectionCode' => $this->collectionCode))
            ->fetchAll();

        $results2 = $this->em->getConnection()
            ->executeQuery($sqlDiff2,
                array('institutionCode' => $this->institutionCode, 'collectionCode' => $this->collectionCode))
            ->fetchAll();

        return array_unique(array_merge($results1, $results2));
    }

    /**
     * @param $class
     * @return string
     */
    private function getFullClassName($class)
    {
        return 'AppBundle:' . $class;
    }

    /**
     * @param int $compt
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generateDiff($compt)
    {
        $randomClassName = $this->getFullClassName($this->entitiesName[array_rand($this->entitiesName, 1)]);
        $metadata = $this->em->getMetadataFactory()->getMetadataFor($randomClassName);
        $repository = $this->em->getRepository($randomClassName);
        $identifier = $metadata->getIdentifierFieldNames() [0];

        $entity = $repository->createQueryBuilder('e')
            ->orderBy('RAND()')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
        $fields = $metadata->getFieldNames();

        // On enleve le champ de l'indentifiant
        unset($fields[array_search($identifier, $fields)]);
        unset($fields[array_search('dwcaid', $fields)]);
        unset($fields[array_search('hasmedia', $fields)]);
        unset($fields[array_search('modified', $fields)]);
        unset($fields[array_search('catalognumber', $fields)]);
        unset($fields[array_search('institutioncode', $fields)]);
        unset($fields[array_search('sourcefileid', $fields)]);

        shuffle($fields);
        $randomFields = array_slice($fields, 0, $compt);
        $fieldMappings = $this->em->getClassMetadata($randomClassName)->fieldMappings;
        foreach ($randomFields as $fieldName) {
            $setter = 'set' . $fieldName;
            if ($fieldName[strlen($fieldName) - 1] == '_') {
                $setter = 'set' . substr($fieldName, 0, -1);
            }
            $entity->{$setter}($this->getFakeData($metadata->getTypeOfField($fieldName),
                $fieldMappings[$fieldName]['length']));

        }
        $this->em->flush($entity);


        return $randomFields;
    }

    /**
     * @param $type
     * @param null $length
     * @return \DateTime|mixed|string
     */
    private function getFakeData($type, $length = null)
    {
        switch ($type) {
            case 'string':
            case 'text':
                $arrayString = ['lorem', 'lorem ipsum', 'blabla', 'text sample'];
                $returnString = $arrayString[array_rand($arrayString)];
                if (!is_null($length)) {
                    $returnString = substr($returnString, 0, $length);
                }
                return $returnString;
            case 'integer':
                if ($length === null) {
                    $length = 2;
                }
                $arrayInt = range(10, (10 * $length));
                return $arrayInt[array_rand($arrayInt)];
            case 'float':
                if ($length === null) {
                    $length = 2;
                }
                $arrayFloat = range(10, (10 * $length), 0.1);
                return $arrayFloat[array_rand($arrayFloat)];
            case 'datetime':
                $arrayDate = ['1976-11-08', '2005-12-25', '1953-01-31', '2006-12-13'];
                return new \DateTime($arrayDate[array_rand($arrayDate)]);
            case 'boolean':
                return array_rand([true, false]);
        }
    }
}
