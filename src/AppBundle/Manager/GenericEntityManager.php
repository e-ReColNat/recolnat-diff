<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Collection;
use AppBundle\Entity\Repository\Abstracts\AbstractRecolnatRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Intl\Locale;

/**
 * Description of EntityManager
 *
 * @author tpateffoz
 */
class GenericEntityManager
{

    /**
     * @var EntityManager
     */
    protected $emR;

    /**
     * @var EntityManager
     */
    protected $emB;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;
    protected $stats = array();
    protected $excludeFieldsName = [];
    protected $maxNbSpecimenPerPass ;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * GenericEntityManager constructor.
     * @param ManagerRegistry $managerRegistry
     * @param int $maxNbSpecimenPerPass
     * @param LoggerInterface $logger
     */
    public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger, $maxNbSpecimenPerPass)
    {
        $this->managerRegistry = $managerRegistry;
        $this->emR = $managerRegistry->getManager('default');
        $this->emB = $managerRegistry->getManager('buffer');
        $this->maxNbSpecimenPerPass = $maxNbSpecimenPerPass;
        $this->logger = $logger;
    }

    /**
     * @param string $base
     * @param string $className
     * @param string $id
     * @return null|object
     */
    public function getEntity($base, $className, $id)
    {
        $em = $this->getEntityManager($base);
        $entity = $em->getRepository($this->getFullClassName($className))->find($id);

        return $entity;
    }

    /**
     * @param string|object $entity
     * @return string
     * @throws \Exception
     */
    public function getIdentifierName($entity)
    {
        $entity = $this->getEntityClass($entity);
        $meta = $this->emR->getClassMetadata(get_class($entity));
        $identifier = $meta->getSingleIdentifierFieldName();

        return $identifier;
    }

    /**
     * @param mixed $entity
     * @return mixed
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Exception
     */
    public function getIdentifierValue($entity)
    {
        $entity = $this->getEntityClass($entity);
        $meta = $this->emR->getClassMetadata(get_class($entity));
        $identifier = $meta->getSingleIdentifierFieldName();
        $getter = 'get'.$identifier;

        return $entity->{$getter}();
    }

    /**
     * @param $entity
     * @return mixed
     * @throws \Exception
     */
    private function getEntityClass($entity)
    {
        if (!is_object($entity)) {
            $fullClassName = $this->getFullClassName($entity);
            if (class_exists($fullClassName)) {
                $entity = new $fullClassName;

                return $entity;
            } else {
                throw new \Exception(sprintf('class %s n\'existe pas', $fullClassName));
            }
        }

        return $entity;
    }

    /**
     * @param string     $base
     * @param Collection $collection
     * @param string     $className
     * @param array      $catalogNumbers
     * @param boolean   $export
     * @return mixed
     */
    public function getEntitiesByCatalogNumbers($base, Collection $collection, $className, $catalogNumbers, $export = false)
    {
        $em = $this->getEntityManager($base);

        $mergeEntities = [];
        $arrayChunkCatalogNumbers = array_chunk($catalogNumbers, $this->maxNbSpecimenPerPass);
        $outputInterface = new ConsoleOutput();
        if (count($arrayChunkCatalogNumbers)) {
            $countCatalogNumbers = count($catalogNumbers);
            $outputInterface->writeln(sprintf('catalogNumbers : %d', $countCatalogNumbers));
            $countChunkCatalogNumbers = 0;
            foreach ($arrayChunkCatalogNumbers as $chunkCatalogNumbers) {
                $countChunkCatalogNumbers+=count($chunkCatalogNumbers);
                $outputInterface->writeln(sprintf('catalogNumbers : %d/%d', $countChunkCatalogNumbers, $countCatalogNumbers));
                $debut = microtime(true);
                /** @var AbstractRecolnatRepository $repo */
                $repo = $em->getRepository($this->getFullClassName($className));
                if ($export) {
                    $entities = $repo->findByCatalogNumbersForExport($collection, $chunkCatalogNumbers, AbstractQuery::HYDRATE_ARRAY);
                }
                else {
                    $entities = $repo->findByCatalogNumbers($collection, $chunkCatalogNumbers, AbstractQuery::HYDRATE_ARRAY);
                }
                $mergeEntities = array_merge($entities, $mergeEntities);
                $this->logger->debug(sprintf('request %d records duree : %s ', count($chunkCatalogNumbers), microtime(true)-$debut));
            }
        }

        return $mergeEntities;
    }

    /**
     * @param string $classname
     * @return string
     */
    public function formatClassName($classname)
    {
        return ucfirst(strtolower($classname));
    }

    /**
     * @param string     $base
     * @param Collection $collection
     * @param array      $catalogNumbers
     * @param boolean   $export
     * @return mixed
     */
    public function getEntitiesLinkedToSpecimens($base, Collection $collection, $catalogNumbers, $export = false)
    {
        return $this->getEntitiesByCatalogNumbers($base, $collection, 'Specimen', $catalogNumbers, $export);
    }


    /**
     * Parcourt tout le tableaux des spécimens pour l'export et convertit tous les RAWID en GUID
     * ex ; e24a3400afe84b0aacde917db0be882b => e24a3400-afe8-4b0a-acde-917db0be882b (8-4-4-4-12)
     * @param array $specimen
     * @return array
     */
    private function convertAllRawIdsToGuids(array $specimen)
    {
        $specimen['Specimen']['occurrenceid'] = UtilityService::formatRawId($specimen['Specimen']['occurrenceid']);
        if (count($specimen['Multimedia'])) {
            foreach ($specimen['Multimedia'] as $key => $item) {
                $specimen['Multimedia'][$key]['multimediaid'] = UtilityService::formatRawId($item['multimediaid']);
            }
        }
        if (count($specimen['Bibliography'])) {
            foreach ($specimen['Bibliography'] as $key => $item) {
                $specimen['Bibliography'][$key]['referenceid'] = UtilityService::formatRawId($item['referenceid']);
            }
        }
        if (count($specimen['Determination'])) {
            foreach ($specimen['Determination'] as $key => $item) {
                $specimen['Determination'][$key]['identificationid'] = UtilityService::formatRawId($item['identificationid']);
                $specimen['Determination'][$key]['occurrenceid'] = $specimen['Specimen']['occurrenceid'];
            }
        }
        if (!is_null($specimen['Recolte'])) {
            $specimen['Recolte']['eventid'] = UtilityService::formatRawId($specimen['Recolte']['eventid']);
        }

        return $specimen;
    }

    /**
     * @param array $specimen
     * @return array
     */
    private function addKeys(array $specimen)
    {
        if (!is_null($specimen['Recolte'])) {
            $specimen['Specimen']['eventid'] = UtilityService::formatRawId($specimen['Recolte']['eventid']);
        }
        if (!is_null($specimen['Localisation'])) {
            $specimen['Recolte']['locationid'] = $specimen['Localisation']['locationid'];
        }
        if (count($specimen['Bibliography'])) {
            foreach ($specimen['Bibliography'] as $key => $item) {
                $specimen['Bibliography'][$key]['occurrenceid'] = UtilityService::formatRawId($specimen['Specimen']['occurrenceid']);
            }
        }
        if (count($specimen['Multimedia'])) {
            foreach ($specimen['Multimedia'] as $key => $item) {
                $specimen['Multimedia'][$key]['occurrenceid'] = UtilityService::formatRawId($specimen['Specimen']['occurrenceid']);
            }
        }
        if (!is_null($specimen['Stratigraphy'])) {
            $specimen['Specimen']['geologicalcontextid'] = $specimen['Stratigraphy']['geologicalcontextid'];
        }

        return $specimen;
    }

    /**
     * Reformat le tableau généré par doctrine
     * @param array $specimen
     * @return array
     */
    public function formatArraySpecimenForExport(array $specimen)
    {
        $formattedSpecimen = [];
        $formattedSpecimen['Bibliography'] = $specimen['bibliographies'];
        unset($specimen['bibliographies']);

        $formattedSpecimen['Determination'] = $specimen['determinations'];
        foreach ($formattedSpecimen['Determination'] as $key => $determination) {
            $formattedSpecimen['Determination'][$key]['Taxon'] = $formattedSpecimen['Determination'][$key]['taxon'];
            unset($formattedSpecimen['Determination'][$key]['taxon']);
        }
        unset($specimen['determinations']);

        $formattedSpecimen['Multimedia'] = $specimen['multimedias'];
        unset($specimen['multimedias']);

        $formattedSpecimen['Stratigraphy'] = $specimen['stratigraphy'];
        unset($specimen['stratigraphy']);

        $formattedSpecimen['Recolte'] = $specimen['recolte'];
        unset($specimen['recolte']);
        $formattedSpecimen['Localisation'] = $formattedSpecimen['Recolte']['localisation'];
        unset($formattedSpecimen['Recolte']['localisation']);
        unset($specimen['Recolte']['localisation']);

        $formattedSpecimen['Specimen'] = $specimen;

        return $this->convertAllRawIdsToGuids($this->addKeys($formattedSpecimen));
    }

    /**
     * @param string $base
     * @param string $className
     * @param string $fieldName
     * @param string $id
     * @return bool|string
     * @throws \Exception
     */
    public function getData($base, $className, $fieldName, $id)
    {
        $fullClassName = $this->getFullClassName($className);
        $getter = 'get'.$fieldName;
        if (method_exists($fullClassName, $getter)) {
            $em = $this->getEntityManager($base);
            $data = $em->getRepository($fullClassName)->findOneFieldById($className, $id, $fieldName);

            if ($data instanceof \DateTime) {
                $dateFormater = $this->getDateFormatter();
                $data = $dateFormater->format($data);
            }

            return $data;
        } else {
            throw new \Exception('\AppBundle\Entity\\'.$className.' get'.$fieldName.' doesn\'t exists.');
        }
    }

    /**
     * @param string $className
     * @return string
     */
    public function getFullClassName($className)
    {
        return '\\AppBundle\\Entity\\'.$this->formatClassName($className);
    }

    /**
     * @param object $entity
     * @param string $className
     * @param string $fieldName
     * @param string $data
     * @return mixed
     */
    public function setData(&$entity, $className, $fieldName, $data)
    {
        $setter = 'set'.$fieldName;
        if (method_exists($this->getFullClassName($className), $setter)) {
            if ($data instanceof \DateTime) {
                $dateFormater = $this->getDateFormatter();
                $data = $dateFormater->format($data);
            }
            $entity->{$setter}($data);
        }

        return $entity;
    }

    /**
     * @return \IntlDateFormatter
     */
    private function getDateFormatter()
    {
        return \IntlDateFormatter::create(
            Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
    }

    /**
     * @param string $base
     * @return EntityManager
     */
    private function getEntityManager($base)
    {
        $em = $this->emB;
        if (strtolower($base) == 'recolnat') {
            $em = $this->emR;

            return $em;
        }

        return $em;
    }

}
