<?php

namespace AppBundle\Twig;
use Doctrine\ORM\Mapping\ClassMetadataInfo ;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Translation\Translator ;
use Symfony\Component\Translation\DataCollectorTranslator ;
use Symfony\Component\Intl\Locale ;
use Doctrine\Common\Collections\ArrayCollection ;
//use Symfony\Component\Intl\DateFormatter\IntlDateFormatter ;
/**
 * Description of MultimediaExtension
 *
 * @author tpateffoz
 */
class SpecimenExtension extends \Twig_Extension
{
    protected $doctrine;
    protected $translator ;

    public function __construct(RegistryInterface $doctrine,DataCollectorTranslator $translator)
    {
        $this->doctrine = $doctrine;
        $this->translator = $translator;
    }
    
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('relation', array($this, 'getRelation')),
            new \Twig_SimpleFunction('relationById', array($this, 'getRelationById')),
            new \Twig_SimpleFunction('relationByIdToString', array($this, 'getRelationByIdToString')),
            new \Twig_SimpleFunction('fieldToString', array($this, 'getFieldToString')),
        );
    }

    public function getRelation(\AppBundle\Entity\Specimen $specimen, $class) {
        switch (strtolower($class)) {
            case 'specimen' :
                return $specimen ;
            case 'bibliography' :
                return $specimen->getBibliographies() ;
            case 'determination' :
                return $specimen->getDeterminations() ;
            case 'localisation' :
                return $specimen->getRecolte()->getLocalisation() ;
            case 'recolte' :
                return $specimen->getRecolte() ;
            case 'stratigraphy' :
                return $specimen->getStratigraphy() ;
            case 'taxon' :
                $determinations = $specimen->getDeterminations() ;
                $taxons=[];
                foreach ($determinations as $determination) {
                    $taxons[] = $determination->getTaxon() ;
                }
                return $taxons ;
        }
    }

    public function getRelationById(\AppBundle\Entity\Specimen $specimen, $class, $id) {
        $relations = $this->getRelation($specimen, $class) ;
        $return = null ;
        if (!empty($relations)) {
            $metadataInfo = $this->doctrine->getManager()
                    ->getClassMetadata(sprintf('AppBundle:%s', ucfirst($class))) ;
            
            $getter = 'get'.current($metadataInfo->getIdentifier()) ;
            
            if ($relations instanceof \Doctrine\Common\Collections\Collection ||
                $relations instanceof \Doctrine\ORM\PersistentCollection ||
                is_array($relations)
                    ) {
                foreach ($relations as $relation) {
                    if ($relation->{$getter}() == $id) {
                        $return = $relation ;
                    }
                }
            }
            else {
                $return = $relations ;
            }
        }
        return $return ;
    }
    
    public function getRelationByIdToString(\AppBundle\Entity\Specimen $specimen, $class, $id) {
        $relation = $this->getRelationById($specimen, $class, $id) ;
        $toString='';
         if (!is_null($relation)) {
             switch (get_class($relation)) {
                 case '\AppBundle\Entity\Determination' :
                     $toString = $this->getToStringDetermination($relation) ;
                     break;
                 case '\AppBundle\Entity\Recolte' :
                     $toString = $this->getToStringRecolte($relation) ;
                     break;
                 default :
                     $toString = $relation->__toString();
             }
        }
        return $toString ;
    }
    
    private function getToStringRecolte(\AppBundle\Entity\Recolte $recolte) {
        $dateFormater = $this->getDateFormatter() ;
        if (!is_null($recolte->getEventdate())) {
            return sprintf('%s %s', $dateFormater->format($recolte->getEventdate()), $recolte->getRecordedby());
        }
        else {
            return sprintf('%s', $recolte->getRecordedby());
        }
    }
    
    private function getToStringDetermination(\AppBundle\Entity\Determination $determination) {
        $dateFormater = $this->getDateFormatter() ;
        if (!is_null($determination->getDateidentified())) {
            return sprintf('%s %s %s', 
                $determination->getIdentifiedby(), 
                $dateFormater->format($determination->getDateidentified()), 
                $determination->getIdentificationverifstatus()); 
        }
        else {
           return sprintf('%s %s', 
                $determination->getIdentifiedby(), 
                $determination->getIdentificationverifstatus()); 
        }
    }
    
    public function getFieldToString($entity, $fieldName) {
        $returnString = '';
        $getter = 'get'.$fieldName ;
        if (!is_null($entity) && !is_null($fieldName) && method_exists($entity, $getter)) {
            $value = $entity->{$getter}() ;
            if ($value instanceof \DateTime) {
                $dateFormater = $this->getDateFormatter() ;
                $returnString = $dateFormater->format($value) ;
            }
            else {
                $returnString = $value ;
            }
        }
        return $returnString ;
    }
    
    private function getDateFormatter() {
        return \IntlDateFormatter::create(
                    Locale::getDefault(), 
                    \IntlDateFormatter::MEDIUM, 
                    \IntlDateFormatter::NONE) ;
    }
    public function getName()
    {
        return 'specimen_extension';
    }
}
