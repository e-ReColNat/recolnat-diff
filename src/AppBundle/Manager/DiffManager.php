<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Repository\Abstracts\AbstractRecolnatRepository;
use Doctrine\ORM\EntityManager;
use \Doctrine\Common\Persistence\ManagerRegistry;

class DiffManager
{
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

    /** @var  string */
    protected $collectionCode;

    /**
     * Holds the Doctrine entity manager for database interaction
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;


    protected $recolnatAlias;
    protected $recolnatBufferAlias;

    /**
     * @var \DateTime
     */
    protected $startDate;

    private $resultSet;

    /**
     * DiffManager constructor.
     * @param ManagerRegistry $managerRegistry
     * @param string          $recolnatAlias
     * @param string          $recolnatBufferAlias
     */
    public function __construct(
        $recolnatAlias,
        $recolnatBufferAlias,
        ManagerRegistry $managerRegistry
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->em = $managerRegistry->getManager('default');
        $this->recolnatAlias = $recolnatAlias;
        $this->recolnatBufferAlias = $recolnatBufferAlias;
    }


    public function harvestDiffs()
    {
        $this->resultSet = $this->em->getConnection()
            ->executeQuery($this->getQuery(),
                array(
                    'collectionCode' => $this->collectionCode,
                    'startDate' => $this->getStartDate()->format('d-m-Y')
                ))
            ->fetchAll();
    }

    /**
     * @param string $className
     * @return array
     */
    public function getResultByClassName($className)
    {
        $catalogNumberByClassName = [];
        $metadata = $this->em->getMetadataFactory()->getMetadataFor($this->getFullClassName($className));
        $identifier = strtoupper(key(array_flip($metadata->getIdentifier())));
        if (count($this->resultSet)) {
            foreach ($this->resultSet as $item) {
                if (!is_null($item[$identifier])) {
                    $catalogNumberByClassName[] = $item['CATALOGNUMBER'];
                }
            }
        }

        return array_unique($catalogNumberByClassName);
    }

    /**
     * @param string $class
     * @return string
     */
    private function getFullClassName($class)
    {
        return AbstractRecolnatRepository::ENTITY_PREFIX.ucfirst(strtolower($class));
    }

    private function getQuery()
    {
        return <<<QUERY
SELECT
s.catalognumber,
CASE WHEN s.modified >= to_date(:startDate, 'dd-mm-yyyy') THEN s.occurrenceid ELSE NULL END AS occurrenceid,
CASE WHEN BIBLIOGRAPHIES.modified >= s.modified THEN BIBLIOGRAPHIES.referenceid ELSE NULL END AS referenceid,
CASE WHEN RECOLTES.modified >= s.modified THEN RECOLTES.eventid ELSE NULL END AS eventid,
CASE WHEN LOCALISATIONS.modified >= s.modified THEN LOCALISATIONS.locationid ELSE NULL END AS locationid,
CASE WHEN DETERMINATIONS.modified >= s.modified THEN DETERMINATIONS.identificationid ELSE NULL END AS identificationid,
CASE WHEN MULTIMEDIA.modified >= s.modified THEN MULTIMEDIA.MULTIMEDIAID ELSE NULL END AS MULTIMEDIAID,
CASE WHEN STRATIGRAPHIES.modified >= s.modified THEN STRATIGRAPHIES.GEOLOGICALCONTEXTID ELSE NULL END AS GEOLOGICALCONTEXTID,
CASE WHEN TAXONS.modified > s.modified THEN TAXONS.TAXONID ELSE NULL END AS TAXONID
FROM SPECIMENS s
LEFT JOIN BIBLIOGRAPHIES on s.OCCURRENCEID = BIBLIOGRAPHIES.OCCURRENCEID
LEFT JOIN RECOLTES on s.EVENTID = RECOLTES.EVENTID
LEFT JOIN LOCALISATIONS on RECOLTES.LOCATIONID = LOCALISATIONS.LOCATIONID
LEFT JOIN DETERMINATIONS on s.OCCURRENCEID = DETERMINATIONS.OCCURRENCEID
LEFT JOIN MULTIMEDIA_HAS_OCCURRENCES on s.OCCURRENCEID = MULTIMEDIA_HAS_OCCURRENCES.OCCURRENCEID
LEFT JOIN MULTIMEDIA on MULTIMEDIA_HAS_OCCURRENCES.MULTIMEDIAID = MULTIMEDIA.MULTIMEDIAID
LEFT JOIN STRATIGRAPHIES ON s.GEOLOGICALCONTEXTID = STRATIGRAPHIES.GEOLOGICALCONTEXTID
LEFT JOIN TAXONS on DETERMINATIONS.TAXONID = TAXONS.TAXONID
WHERE COLLECTIONCODE=:collectionCode AND (
s.modified >= to_date(:startDate, 'dd-mm-yyyy') OR
BIBLIOGRAPHIES.modified >= to_date(:startDate, 'dd-mm-yyyy') OR
RECOLTES.modified >= to_date(:startDate, 'dd-mm-yyyy') OR
LOCALISATIONS.modified >= to_date(:startDate, 'dd-mm-yyyy') OR
DETERMINATIONS.modified >= to_date(:startDate, 'dd-mm-yyyy') OR
MULTIMEDIA.modified >= to_date(:startDate, 'dd-mm-yyyy') OR
STRATIGRAPHIES.modified >= to_date(:startDate, 'dd-mm-yyyy') OR
TAXONS.modified >= to_date(:startDate, 'dd-mm-yyyy')
)
QUERY;


    }

    /**
     * @param string $collectionCode
     */
    public function setCollectionCode($collectionCode)
    {
        $this->collectionCode = $collectionCode;
    }


    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        $minCollectionModifiedDate = $this->em->getRepository('AppBundle:Specimen')
            ->getMinDate($this->collectionCode);

        if (!is_null($minCollectionModifiedDate) && $minCollectionModifiedDate instanceof \DateTime) {
            if ($minCollectionModifiedDate > $this->startDate) {
                return $minCollectionModifiedDate;
            }
        }

        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }


}
