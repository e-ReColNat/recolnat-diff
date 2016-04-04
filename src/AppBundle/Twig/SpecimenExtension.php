<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Determination;
use AppBundle\Entity\Localisation;
use AppBundle\Entity\Recolte;
use AppBundle\Entity\Specimen;
use AppBundle\Entity\Stratigraphy;
use AppBundle\Entity\Taxon;
use AppBundle\Entity\Collection as rCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Intl\Locale;
use Symfony\Component\Translation\TranslatorInterface;

class SpecimenExtension extends \Twig_Extension
{

    protected $doctrine;
    protected $translator;
    protected $urlRecolnat;

    public function __construct(RegistryInterface $doctrine, TranslatorInterface $translator, $urlRecolnat)
    {
        $this->doctrine = $doctrine;
        $this->translator = $translator;
        $this->urlRecolnat = $urlRecolnat;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('relation', array($this, 'getRelation')),
            new \Twig_SimpleFunction('relationById', array($this, 'getRelationById')),
            new \Twig_SimpleFunction('relationByIdToString', array($this, 'getRelationByIdToString')),
            new \Twig_SimpleFunction('fieldToString', array($this, 'getFieldToString')),
            new \Twig_SimpleFunction('getTaxon', array($this, 'getTaxon')),
            new \Twig_SimpleFunction('printLabelAndField', array($this, 'printLabelAndField')),
            new \Twig_SimpleFunction('getFieldLabel', array($this, 'getFieldLabel')),
            new \Twig_SimpleFunction('getLink', array($this, 'getLink')),
            new \Twig_SimpleFunction('getFullLink', array($this, 'getFullLink')),
        );
    }

    /**
     * @param Specimen           $specimen
     * @param rCollection $collection
     * @param Taxon|null         $taxon
     * @param string             $target
     * @return string
     */
    public function getFullLink(Specimen $specimen,rCollection $collection,Taxon $taxon = null,$target = '_blank')
    {
        $text = '';
        if (!is_null($taxon)) {
            !empty($taxon->getFamily()) ? $text .= '<span><span>'.$taxon->getFamily().'</span> / </span>' : '';
            !empty($taxon->getGenus()) ? $text .= '<span><i><span>'.$taxon->getGenus().'</span></i> / </span>' : '';
            !empty($taxon->getScientificname()) ? $text .= '<span><i><span>'.$taxon->getScientificname().'</span></i> / </span>' : '';
            !empty($taxon->getScientificnameauthorship()) ? $text .= '<span><i><span>'.$taxon->getScientificnameauthorship().'</span></i> / </span>' : '';
            !empty($specimen->getCatalognumber()) ? $text .= '<span>'.$specimen->getCatalognumber().'</span>' : '';
        } else {
            $text = $this->translator->trans('label.notaxon');
        }

        return sprintf('<a href="%s" target="%s">%s</a>', $this->getLink($specimen, $collection), $target, $text);
    }

    /**
     * @param Specimen           $specimen
     * @param rCollection $collection
     * @return string
     */
    public function getLink(Specimen $specimen, rCollection $collection)
    {
        $type = null;
        switch (strtolower($collection->getType())) {
            case 'h':
            case 'b':
                $type = 'botanique';
                break;
            case 'z':
                $type = 'zoologie';
                break;
            case 'p':
                $type = 'paleontologie';
                break;
            case 'g':
                $type = 'geographie';
                break;
        }

        return sprintf('%s%s/%s', $this->urlRecolnat, $type, strtoupper($specimen->getOccurrenceid()));
    }

    /**
     * @param Object $entity
     * @param string $typeEntity
     * @param string $fieldName
     * @param bool   $printIfNull
     * @param string $endString
     * @param array  $transParams
     * @return string
     */
    public function printLabelAndField(
        $entity,
        $typeEntity,
        $fieldName,
        $printIfNull = true,
        $endString = '',
        $transParams = []
    ) {
        $value = $this->getFieldToString($entity, $fieldName);
        if ($printIfNull || !is_null($value)) {
            $label = $this->getFieldLabel($typeEntity, $fieldName);

            return sprintf('%s  : <span>%s</span>%s', $this->translator->trans($label, $transParams, 'entity'), $value,
                $endString);
        }

        return '';
    }

    /**
     * @param Specimen $specimen
     * @param String   $class
     * @return Localisation|Recolte|Specimen|Stratigraphy|array|ArrayCollection
     */
    public function getRelation(Specimen $specimen, $class)
    {
        $relation = null;
        switch (strtolower($class)) {
            case 'specimen':
                $relation = $specimen;
                break;
            case 'bibliography':
                $relation = $specimen->getBibliographies();
                break;
            case 'determination':
                $relation = $specimen->getDeterminations();
                break;
            case 'localisation':
                $relation = $specimen->getRecolte()->getLocalisation();
                break;
            case 'recolte':
                $relation = $specimen->getRecolte();
                break;
            case 'stratigraphy':
                $relation = $specimen->getStratigraphy();
                break;
            case 'multimedia':
                $relation = $specimen->getMultimedias();
                break;
            case 'taxon':
                $determinations = $specimen->getDeterminations();
                $taxons = [];
                foreach ($determinations as $determination) {
                    $taxons[] = $determination->getTaxon();
                }
                $relation = $taxons;
                break;
        }

        return $relation;
    }

    /**
     * @param Specimen $specimen
     * @param string   $class
     * @param string   $id
     * @return Localisation|Recolte|Stratigraphy|array|mixed|null|object
     */
    public function getRelationById(Specimen $specimen, $class, $id)
    {
        $relations = $this->getRelation($specimen, $class);
        $return = null;
        if (!empty($relations)) {
            $metadataInfo = $this->doctrine->getManager()
                ->getClassMetadata(sprintf('AppBundle:%s', ucfirst($class)));

            $getter = 'get'.current($metadataInfo->getIdentifier());

            if ($relations instanceof Collection ||
                $relations instanceof PersistentCollection ||
                is_array($relations)
            ) {
                foreach ($relations as $relation) {
                    if ($relation->{$getter}() == $id) {
                        $return = $relation;
                    }
                }
            } else {
                $return = $relations;
            }
        }

        return $return;
    }

    /**
     * Renvoie le nom minimum d'une extension d'un specimen
     * ex : pour la Récolte d'un specimen on aura la date et nom d'un récolteur
     * @param Specimen $specimen
     * @param string   $class
     * @param string   $id
     * @return string
     */
    public function getRelationByIdToString(Specimen $specimen, $class, $id)
    {
        $relation = $this->getRelationById($specimen, $class, $id);
        $toString = '';
        if (!is_null($relation)) {
            switch (get_class($relation)) {
                case '\AppBundle\Entity\Determination':
                    $toString = $this->getToStringDetermination($relation);
                    break;
                case '\AppBundle\Entity\Recolte':
                    $toString = $this->getToStringRecolte($relation);
                    break;
                default:
                    $toString = $relation->__toString();
            }
        }

        return $toString;
    }

    /**
     * @param Recolte $recolte
     * @return string
     */
    private function getToStringRecolte(Recolte $recolte)
    {
        $dateFormater = $this->getDateFormatter();
        if (!is_null($recolte->getEventdate())) {
            return sprintf('%s %s', $dateFormater->format($recolte->getEventdate()), $recolte->getRecordedby());
        } else {
            return sprintf('%s', $recolte->getRecordedby());
        }
    }

    /**
     * @param Determination $determination
     * @return string
     */
    private function getToStringDetermination(Determination $determination)
    {
        $dateFormater = $this->getDateFormatter();
        if (!is_null($determination->getDateidentified())) {
            return sprintf('%s %s %s', $determination->getIdentifiedby(),
                $dateFormater->format($determination->getDateidentified()),
                $determination->getIdentificationverifstatus());
        } else {
            return sprintf('%s %s', $determination->getIdentifiedby(), $determination->getIdentificationverifstatus());
        }
    }

    /**
     * @param string $entity
     * @param string $fieldname
     * @return string
     */
    public function getFieldLabel($entity, $fieldname)
    {
        if ($fieldname[strlen($fieldname) - 1] == '_') {
            $fieldname = substr($fieldname, 0, -1);
        }

        return sprintf('label.%s.fields.%s', strtolower($entity), strtolower($fieldname));
    }

    /**
     * @param $entity
     * @param $fieldName
     * @return bool|null|string
     */
    public function getFieldToString($entity, $fieldName)
    {
        $returnString = null;
        $getter = 'get'.$fieldName;
        if (!is_null($entity) && !is_null($fieldName) && method_exists($entity, $getter)) {
            $value = $entity->{$getter}();
            if ($value instanceof \DateTime) {
                $dateFormater = $this->getDateFormatter();
                $returnString = $dateFormater->format($value);
            } else {
                $returnString = $value;
            }
        }

        return $returnString;
    }

    /**
     * @param Specimen $specimen
     * @return Taxon|null
     */
    public function getTaxon(Specimen $specimen)
    {
        $determinations = $specimen->getDeterminations();
        if (count($determinations) > 0) {
            $taxon = $determinations[0]->getTaxon();
            if ($taxon !== null) {
                return $taxon;
            } else {
                return null;
            }
        }
    }

    /**
     * @return \IntlDateFormatter|\Symfony\Component\Intl\DateFormatter\IntlDateFormatter
     */
    private function getDateFormatter()
    {
        return \IntlDateFormatter::create(
            Locale::getDefault(), \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'specimen_extension';
    }

}
