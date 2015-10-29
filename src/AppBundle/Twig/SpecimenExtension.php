<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Twig;

/**
 * Description of MultimediaExtension
 *
 * @author tpateffoz
 */
class SpecimenExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('relation', array($this, 'getRelation'))
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
                $taxons = [];
                foreach ($determinations as $determination) {
                    $taxons[] = $determination->getTaxon() ;
                }
                return $taxons ;
        }
    }

    public function getName()
    {
        return 'specimen_extension';
    }
}
