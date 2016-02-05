<?php

namespace AppBundle\Manager;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Repository\RecolnatRepositoryAbstract;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Intl\Locale;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

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
    protected $emI;
    protected $translator;
    protected $stats = array();
    protected $excludeFieldsName = [];

    public function __construct(EntityManager $emR, EntityManager $emI, DataCollectorTranslator $translator)
    {
        $this->emR = $emR;
        $this->emI = $emI;
        $this->translator = $translator;
    }

    public function getEntity($base, $className, $id)
    {
        $em = $this->emI;
        if (strtolower($base) == 'recolnat') {
            $em = $this->emR;
        }
        $entity = $em->getRepository($this->getFullClassName($className))->find($id);
        return $entity;
    }

    public function getIdentifierName($entity)
    {
        if (!is_object($entity)) {
            try {
                $fullClassName = $this->getFullClassName($entity);
                $entity = new $fullClassName;
            } catch (\Exception $ex) {
                throw new \Exception(sprintf('class %s n\'existe pas', $this->getFullClassName($entity)));
            }
        }
        $meta = $this->emR->getClassMetadata(get_class($entity));
        $identifier = $meta->getSingleIdentifierFieldName();
        return $identifier;
    }

    public function getIdentifierValue($entity)
    {
        if (!is_object($entity)) {
            try {
                $fullClassName = $this->getFullClassName($entity);
                $entity = new $fullClassName;
            } catch (\Exception $ex) {
                throw new \Exception(sprintf('class %s n\'existe pas', $this->getFullClassName($entity)));
            }
        }
        $meta = $this->emR->getClassMetadata(get_class($entity));
        $identifier = $meta->getSingleIdentifierFieldName();
        $getter = 'get' . $identifier;
        return $entity->{$getter}();
    }

    public function getEntitiesBySpecimenCodes($base, $className, $specimenCodes)
    {
        $em = $this->emI;
        if (strtolower($base) == 'recolnat') {
            $em = $this->emR;
        }
        $entities = $em->getRepository($this->getFullClassName($className))->findAllBySpecimenCodeUnordered($specimenCodes);
        return $entities;
    }

    public function formatClassName($classname)
    {
        return ucfirst(strtolower($classname));
    }

    public function getEntitiesLinkedToSpecimens($base, $specimenCodes)
    {
        return $this->getEntitiesBySpecimenCodes($base, 'Specimen', $specimenCodes);
    }


    /**
     * Reformat le tableau généré par doctrine 
     * @param array $specimen
     * @return array
     */
    public function formatArraySpecimen(array $specimen) {
        $formattedSpecimen = [] ;
        $formattedSpecimen['Bibliography'] = $specimen['bibliographies'] ;
        unset($specimen['bibliographies']);
        
        $formattedSpecimen['Determination'] = $specimen['determinations'] ;
        foreach($formattedSpecimen['Determination'] as $key => $determination) {
            $formattedSpecimen['Determination'][$key]['Taxon'] = $formattedSpecimen['Determination'][$key]['taxon'] ;
            unset($formattedSpecimen['Determination'][$key]['taxon']);
        }
        unset($specimen['determinations']);
        
        $formattedSpecimen['Multimedia'] = $specimen['multimedias'] ;
        unset($specimen['multimedias']);
        
        $formattedSpecimen['Stratigraphy'] = $specimen['stratigraphy'] ;
        unset($specimen['stratigraphy']);
        
        $formattedSpecimen['Recolte'] = $specimen['recolte'] ;
        unset($specimen['recolte']);
        $formattedSpecimen['Localisation'] = $formattedSpecimen['Recolte']['localisation'] ;
        unset($formattedSpecimen['Recolte']['localisation']);
        unset($specimen['Recolte']['localisation']);
        
        $formattedSpecimen['Specimen'] = $specimen ;
        
        return $formattedSpecimen ;
    }

    public function getData($base, $className, $fieldName, $id)
    {
        $fullClassName = $this->getFullClassName($className);
        $getter = 'get' . $fieldName;
        if (method_exists($fullClassName, $getter)) {
            $em = $this->emI;
            if (strtolower($base) == 'recolnat') {
                $em = $this->emR;
            }
            $entity = $em->getRepository($fullClassName)->find($id);

            $data = $entity->{$getter}();
            if ($data instanceof \DateTime) {
                $dateFormater = $this->getDateFormatter();
                $data = $dateFormater->format($data);
            }
            return $data;
        } else {
            throw new \Exception('\AppBundle\Entity\\' . $className. ' get' . $fieldName . ' doesn\'t exists.');
        }
    }

    public function getFullClassName($className)
    {
        return '\\AppBundle\\Entity\\' . $this->formatClassName($className);
    }

    public function setData(&$entity, $className, $fieldName, $data)
    {
        $setter = 'set' . $fieldName;
        if (method_exists($this->getFullClassName($className), $setter)) {
            if ($data instanceof \DateTime) {
                $dateFormater = $this->getDateFormatter();
                $data = $dateFormater->format($data);
            }
            $entity->{$setter}($data);
        }
        return $entity;
    }

    private function getDateFormatter()
    {
        return \IntlDateFormatter::create(
                        Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
    }
}
