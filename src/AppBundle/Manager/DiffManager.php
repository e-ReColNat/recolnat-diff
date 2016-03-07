<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
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

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var Collection
     */
    protected $collection;

    const LENGTH_TEXT = 4000;
    const SPECIMEN_CLASSNAME = 'AppBundle:Specimen';
    const RECOLTE_CLASSNAME = 'AppBundle:Recolte';
    const DETERMINATION_CLASSNAME = 'AppBundle:Determination';

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

    protected $recolnat_alias;
    protected $recolnat_diff_alias;

    /**
     * DiffManager constructor.
     * @param ManagerRegistry $managerRegistry
     * @param string          $recolnat_alias
     * @param string          $recolnat_diff_alias
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $recolnat_alias,
        $recolnat_diff_alias
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->em = $managerRegistry->getManager('default');
        $this->recolnat_alias = $recolnat_alias;
        $this->recolnat_diff_alias = $recolnat_diff_alias;
    }

    /**
     * @param Collection $collection
     * @return array
     */
    public function init(Collection $collection)
    {
        $this->collection = $collection;
        $diffs = $this->getAllDiff();
        return $diffs;
    }

    /**
     * @return array
     */
    private function getAllDiff()
    {
        $results = [];
        foreach ($this->entitiesName as $entityName) {
            $results[$entityName] = $this->getDiff($entityName);
        }
        return $results;
    }

    /**
     * @param string  $fullClassName
     * @param array   $db1
     * @param array   $db2
     * @param boolean $inversed
     * @return string
     */
    private function getGenericDiffQuery($fullClassName, $db1, $db2, $inversed)
    {
        /* @var $metadata \Doctrine\ORM\Mapping\ClassMetadata */
        $metadata = $this->em->getMetadataFactory()->getMetadataFor($fullClassName);

        $aliasDb1 = $db1['alias'];
        $aliasDb2 = $db2['alias'];

        $forceIndex = [
            'AppBundle:Localisation' => ' /*+ INDEX(RECOLTES RECOLTES_PK) */ ',
            'AppBundle:Determination' => ' /*+ INDEX(DETERMINATIONS DETER_SPEC_IDX_FK) */ ',
            //'AppBundle:Taxon' => ' /*+ INDEX(DETERMINATIONS DETER_TAX_IDX_FK) */ '
        ];
        isset ($forceIndex[$fullClassName]) ? $strForceIndex = $forceIndex[$fullClassName] : $strForceIndex = '';
        $identifier = 'specimenCode';
        $strQuery = 'SELECT '.$identifier.' FROM ';
        $arrayFields = $this->formatFieldsName($metadata, $aliasDb1, $aliasDb2);
        $strFromClauseDb1 = $this->getFromClause($fullClassName, $aliasDb1, $inversed);
        $strFromClauseDb2 = $this->getFromClause($fullClassName, $aliasDb2, !$inversed);
        $strUnionQuery =
            $strQuery.'('.
            'SELECT '.$strForceIndex.
            implode(', ', $arrayFields['db1']).
            sprintf($strFromClauseDb1, $db1['name'].'.'.$metadata->getTableName()).
            ' MINUS '.
            'SELECT '.$strForceIndex.
            implode(', ', $arrayFields['db2']).
            sprintf($strFromClauseDb2, $db2['name'].'.'.$metadata->getTableName())
            .')';
        return sprintf($strUnionQuery, $identifier, $identifier, $identifier);
    }

    /**
     * @param string $alias
     * @return string
     */
    private function getSpecimenUniqueIdClause($alias)
    {
        return sprintf(' %s.institutioncode||\'#\'||%s.collectioncode||\'#\'||%s.catalognumber as specimenCode ',
            $alias, $alias,
            $alias);
    }

    /**
     * @param ClassMetadata $metadata
     * @param string        $aliasDb1
     * @param string        $aliasDb2
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
            if (strtolower($metadata->getTypeOfField($fieldName)) === 'text' || strtolower($fieldName) == 'identificationremarks') {
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
                    // Taxon
                case 'class':
                case 'order':
                    $arrayFieldsTypeDb1[$key] = sprintf('%s.%s', $aliasDb1, $fieldName.'_');
                    $arrayFieldsTypeDb2[$key] = sprintf('%s.%s', $aliasDb2, $fieldName.'_');
                    break;
                case 'determinations':
                case 'sourcefileid':
                case 'hascoordinates':
                case 'dwcataxonid':
                case 'dwcaid':
                case 'hasmedia':
                case 'created':
                case 'modified':
                case 'eventdate':
                    unset($arrayFieldsTypeDb1[$key]);
                    unset($arrayFieldsTypeDb2[$key]);
                    break;
            }
        }
        $aliasSpecimenDb1 = 's';
        $aliasSpecimenDb2 = 's';
        if ($metadata->getName() == 'AppBundle\Entity\Specimen') {
            $aliasSpecimenDb1 = $aliasDb1;
            $aliasSpecimenDb2 = $aliasDb2;
        }
        $arrayFieldsTypeDb1[] = $this->getSpecimenUniqueIdClause($aliasSpecimenDb1);
        $arrayFieldsTypeDb2[] = $this->getSpecimenUniqueIdClause($aliasSpecimenDb2);

        return ['db1' => $arrayFieldsTypeDb1, 'db2' => $arrayFieldsTypeDb2];
    }

    /**
     * @param string $fullClassName
     * @param string $alias
     * @param bool   $institution
     * @return string
     */
    private function getFromClause($fullClassName, $alias, $institution)
    {
        $metadataSpecimen = $this->em->getMetadataFactory()->getMetadataFor(self::SPECIMEN_CLASSNAME);
        $specimenTableName = ($institution === true ? $this->recolnat_diff_alias : $this->recolnat_alias).'.'.$metadataSpecimen->getTableName();

        $fromClause = '';
        switch ($fullClassName) {
            case 'AppBundle:Specimen':
                $fromClause = ' FROM %s '.$alias.' WHERE '.$this->getJoinCodeSpecimen($alias);
                break;
            case 'AppBundle:Bibliography':
            case 'AppBundle:Determination':
                $fromClause = ' FROM %s '.$alias.' INNER JOIN '.$specimenTableName.' s ON s.OCCURRENCEID = '
                    .$alias.'.OCCURRENCEID AND '
                    .$this->getJoinCodeSpecimen();
                break;
            case 'AppBundle:Localisation':
                $metadataRecolte = $this->em->getMetadataFactory()->getMetadataFor(self::RECOLTE_CLASSNAME);
                $recolteTableName = ($institution === true ? $this->recolnat_diff_alias : $this->recolnat_alias).'.'.$metadataRecolte->getTableName();
                $fromClause = ' FROM %s '.$alias
                    .' INNER JOIN '.$recolteTableName.' ON '.$recolteTableName.'.LOCATIONID = '.$alias.'.LOCATIONID'
                    .' INNER JOIN '.$specimenTableName.' s ON s.EVENTID = '
                    .$recolteTableName.'.EVENTID AND '
                    .$this->getJoinCodeSpecimen();
                break;
            case 'AppBundle:Recolte':
                $fromClause = ' FROM %s '.$alias.' INNER JOIN '.$specimenTableName.' s ON s.EVENTID = '
                    .$alias.'.EVENTID AND '
                    .$this->getJoinCodeSpecimen();
                break;
            case 'AppBundle:Stratigraphy':
                $fromClause = ' FROM %s '.$alias.' INNER JOIN '.$specimenTableName.' s ON s.GEOLOGICALCONTEXTID = '
                    .$alias.'.GEOLOGICALCONTEXTID AND '
                    .$this->getJoinCodeSpecimen();
                break;
            case 'AppBundle:Taxon':
                $metadataDetermination = $this->em->getMetadataFactory()->getMetadataFor(self::DETERMINATION_CLASSNAME);
                $determinationTableName = ($institution === true ? $this->recolnat_diff_alias : $this->recolnat_alias).'.'.$metadataDetermination->getTableName();
                $fromClause = ' FROM %s '.$alias
                    .' INNER JOIN '.$determinationTableName.' ON '.$determinationTableName.'.TAXONID = '.$alias.'.TAXONID'
                    .' INNER JOIN '.$specimenTableName.' s ON s.OCCURRENCEID = '
                    .$determinationTableName.'.OCCURRENCEID AND '
                    .$this->getJoinCodeSpecimen();
                break;
        }
        return $fromClause;
    }

    private function getJoinCodeSpecimen($alias = 's')
    {
        return sprintf('%s.COLLECTIONID = :collectionId ', $alias);
    }

    /**
     * @param string $className
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getDiff($className)
    {
        $fullClassName = $this->getFullClassName($className);
        $db1 = ['name' => $this->recolnat_alias, 'alias' => 'r'];
        $db2 = ['name' => $this->recolnat_diff_alias, 'alias' => 'i'];
        $sqlDiff1 = $this->getGenericDiffQuery($fullClassName, $db1, $db2, false);
        $sqlDiff2 = $this->getGenericDiffQuery($fullClassName, $db2, $db1, true);

        $this->em->getConnection()->setFetchMode(\PDO::FETCH_COLUMN);
        $results1 = $this->em->getConnection()
            ->executeQuery($sqlDiff1,
                array(
                    'collectionId' => $this->collection->getCollectionid()
                ))
            ->fetchAll();

        $results2 = $this->em->getConnection()
            ->executeQuery($sqlDiff2,
                array(
                    'collectionId' => $this->collection->getCollectionid()
                ))
            ->fetchAll();

        return array_unique(array_merge($results1, $results2));
    }

    /**
     * @param $class
     * @return string
     */
    private function getFullClassName($class)
    {
        return 'AppBundle:'.ucfirst(strtolower($class));
    }

    /**
     * @param Collection $collection
     * @param int        $comptEntities
     * @param int        $comptFields
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generateDiff(Collection $collection, $comptEntities, $comptFields)
    {
        for ($i = 1; $i <= $comptEntities; $i++) {
            $doNotTouchThisFields = [
                'determinations',
                'sourcefileid',
                'hascoordinates',
                'dwcataxonid',
                'dwcaid',
                'hasmedia',
                'created',
                'modified',
                'eventdate',
                'catalognumber',
                'institutioncode',
                'collectioncode',
                'datepublication',
                'occurrenceid'
            ];

            $randomClassName = $this->getFullClassName($this->entitiesName[array_rand($this->entitiesName, 1)]);
            $metadata = $this->em->getMetadataFactory()->getMetadataFor($randomClassName);
            $repository = $this->em->getRepository($randomClassName);
            $identifier = $metadata->getIdentifierFieldNames() [0];

            $doNotTouchThisFields[] = $identifier;

            $id = $repository->getQueryBuilderFindByCollection($collection)
                ->orderBy('RAND()')
                ->setMaxResults(1)
                ->getQuery()->getArrayResult();

            if (!is_null($id)) {
                $fields = $metadata->getFieldNames();

                foreach ($fields as $key => $field) {
                    if (in_array($field, $doNotTouchThisFields)) {
                        unset($fields[$key]);
                    }
                }

                shuffle($fields);
                $datas = [];
                $randomFields = array_slice($fields, 0, $comptFields);
                $fieldMappings = $this->em->getClassMetadata($randomClassName)->fieldMappings;
                foreach ($randomFields as $fieldName) {
                    $datas[$fieldName] = $this->getFakeData($metadata->getTypeOfField($fieldName),
                        $fieldMappings[$fieldName]['length']);

                }
                $repository->update($datas, $id);
            }
        }
    }

    /**
     * @param string $type
     * @param null   $length
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
                $date = new \DateTime($arrayDate[array_rand($arrayDate)]);
                return $date->format('Y-m-d H:i:s');
            case 'boolean':
                return array_rand([true, false]);
        }
    }
}
