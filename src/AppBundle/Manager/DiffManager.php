<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Collection;
use AppBundle\Entity\Repository\AbstractRecolnatRepository;
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
    const MULTIMEDIA_HAS_OCCURRENCES_TABLE_NAME = 'MULTIMEDIA_HAS_OCCURRENCES';
    const ENTITIES_NAME = [
        'Specimen',
        'Bibliography',
        'Determination',
        'Localisation',
        'Recolte',
        'Stratigraphy',
        'Taxon',
        'Multimedia'
    ];

    protected $institutionCode;
    protected $collectionCode;
    protected $entitiesName = [
        'Specimen',
        'Bibliography',
        'Determination',
        'Localisation',
        'Recolte',
        'Stratigraphy',
        'Taxon',
        'Multimedia'
    ];

    protected $recolnatAlias;
    protected $recolnatDiffAlias;

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
        $this->recolnatAlias = $recolnat_alias;
        $this->recolnatDiffAlias = $recolnat_diff_alias;
    }

    /**
     * @param Collection $collection
     */
    public function init(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return array
     */
    public function searchDiffs()
    {
        $catalogNumber = [];
        foreach ($this->entitiesName as $entityName) {
            $catalogNumber[$entityName] = $this->getDiff($entityName);
        }
        return $catalogNumber;
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
        $identifier = 'cn as catalognumber';
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
        return sprintf(' %s.catalognumber as cn', $alias);
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
                case 'explore_url':
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
        $specimenTableName = ($institution === true ? $this->recolnatDiffAlias : $this->recolnatAlias).'.'.$metadataSpecimen->getTableName();

        $fromClause = '';
        switch (str_replace(AbstractRecolnatRepository::ENTITY_PREFIX, '', $fullClassName)) {
            case 'Specimen':
                $fromClause = ' FROM %s '.$alias.' WHERE '.$this->getJoinCodeSpecimen($alias);
                break;
            case 'Bibliography':
            case 'Determination':
                $fromClause = ' FROM %s '.$alias.' INNER JOIN '.$specimenTableName.' s ON s.OCCURRENCEID = '
                    .$alias.'.OCCURRENCEID AND '
                    .$this->getJoinCodeSpecimen();
                break;
            case 'Localisation':
                $metadataRecolte = $this->em->getMetadataFactory()->getMetadataFor(self::RECOLTE_CLASSNAME);
                $recolteTableName = ($institution === true ? $this->recolnatDiffAlias : $this->recolnatAlias).'.'
                    .$metadataRecolte->getTableName();
                $fromClause = ' FROM %s '.$alias
                    .' INNER JOIN '.$recolteTableName.' ON '.$recolteTableName.'.LOCATIONID = '.$alias.'.LOCATIONID'
                    .' INNER JOIN '.$specimenTableName.' s ON s.EVENTID = '
                    .$recolteTableName.'.EVENTID AND '
                    .$this->getJoinCodeSpecimen();
                break;
            case 'Recolte':
                $fromClause = ' FROM %s '.$alias.' INNER JOIN '.$specimenTableName.' s ON s.EVENTID = '
                    .$alias.'.EVENTID AND '
                    .$this->getJoinCodeSpecimen();
                break;
            case 'Stratigraphy':
                $fromClause = ' FROM %s '.$alias.' INNER JOIN '.$specimenTableName.' s ON s.GEOLOGICALCONTEXTID = '
                    .$alias.'.GEOLOGICALCONTEXTID AND '
                    .$this->getJoinCodeSpecimen();
                break;
            case 'Taxon':
                $metadataDetermination = $this->em->getMetadataFactory()->getMetadataFor(self::DETERMINATION_CLASSNAME);
                $determinationTableName = ($institution === true ? $this->recolnatDiffAlias : $this->recolnatAlias).'.'
                    .$metadataDetermination->getTableName();
                $fromClause = ' FROM %s '.$alias
                    .' INNER JOIN '.$determinationTableName.' ON '.$determinationTableName.'.TAXONID = '.$alias.'.TAXONID'
                    .' INNER JOIN '.$specimenTableName.' s ON s.OCCURRENCEID = '
                    .$determinationTableName.'.OCCURRENCEID AND '
                    .$this->getJoinCodeSpecimen();
                break;
            case 'Multimedia':
                $multimediaHasOccurrencesTableName = ($institution === true ? $this->recolnatDiffAlias : $this->recolnatAlias)
                    .'.'.self::MULTIMEDIA_HAS_OCCURRENCES_TABLE_NAME;
                $fromClause = ' FROM %s '.$alias
                    .' INNER JOIN '.$multimediaHasOccurrencesTableName.' ON '.$multimediaHasOccurrencesTableName
                    .'.MULTIMEDIAID = '.$alias.'.MULTIMEDIAID'
                    .' INNER JOIN '.$specimenTableName.' s ON s.OCCURRENCEID = '
                    .$multimediaHasOccurrencesTableName.'.OCCURRENCEID AND '
                    .$this->getJoinCodeSpecimen();
                break;
        }
        return $fromClause;
    }

    private function getModifiedClause()
    {
        return 'CASE
            WHEN recoltes.modified > specimens.modified THEN recoltes.modified
            WHEN localisations.modified > specimens.modified THEN localisations.modified
            WHEN determinations.modified > specimens.modified THEN determinations.modified
            ELSE specimens.modified
        END AS modified' ;
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
    public function getDiff($className)
    {
        $fullClassName = $this->getFullClassName($className);
        $db1 = ['name' => $this->recolnatAlias, 'alias' => 'r'];
        $db2 = ['name' => $this->recolnatDiffAlias, 'alias' => 'i'];
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
        return AbstractRecolnatRepository::ENTITY_PREFIX.ucfirst(strtolower($class));
    }

    /**
     * @param Collection $collection
     * @param int        $comptEntities
     * @param int        $comptFields
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
            /** @var AbstractRecolnatRepository $repository */
            $repository = $this->em->getRepository($randomClassName);
            $identifier = $metadata->getIdentifierFieldNames() [0];

            $doNotTouchThisFields[] = $identifier;

            $id = $repository->getQueryBuilderFindByCollection($collection)
                ->orderBy('RAND()')
                ->setMaxResults(1)
                ->getQuery()->getSingleScalarResult();

            if ($repository->hasRawId(str_replace($repository::ENTITY_PREFIX, '', $randomClassName))) {
                $id = bin2hex($id);
            }


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
