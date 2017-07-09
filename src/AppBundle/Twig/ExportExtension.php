<?php
namespace AppBundle\Twig;

/**
 * Description of ExportExtension
 *
 * @author tpateffoz
 */
class ExportExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('countChoices', array($this, 'getCountChoices')),
            new \Twig_SimpleFunction('countDiffs', array($this, 'getCountDiffs')),
            new \Twig_SimpleFunction('getFirstLetters', array($this, 'getFirstLetters')),
        );
    }

    /**
     * Retourne un tableau de la première lettre des taxons
     * @param array $specimensData
     * @return array
     */
    public function getFirstLetters(array $specimensData)
    {
        $letters = [];
        $withoutTaxon = 0;
        if (isset($specimensData) && count($specimensData)) {
            foreach ($specimensData as $specimen) {
                if (empty($specimen['taxon'])) {
                    $withoutTaxon++;
                } else {
                    $letter = strtoupper($specimen['taxon']{0});
                    isset($letters[$letter]) ? $letters[$letter]++ : $letters[$letter] = 1;
                }
            }
        }
        ksort($letters);
        if ($withoutTaxon > 0) {
            $letters = ['N/A' => $withoutTaxon] + $letters;
        }

        return $letters;
    }

    /**
     * Retourne le nombre de différence trouvées entre les deux bases pour un couple Spécimen / Class
     * @param array  $specimenData
     * @param string $className
     * @return int
     */
    public function getCountDiffs($specimenData, $className)
    {
        $sum = 0;
        if (isset($specimenData[$className])) {
            $sum += isset($specimenData[$className]['fields']) ? count($specimenData[$className]['fields']) : 0 ;
            $sum += isset($specimenData[$className]['lonesomes']) ? count($specimenData[$className]['lonesomes']) : 0 ;
        }

        return $sum;
    }

    /**
     * Retourne le nombre des choix par l'utilisateur dans les diffs
     * @param array  $choices
     * @param string $className
     * @param string $catalogNumber
     * @return integer
     */
    public function getCountChoices($choices, $catalogNumber, $className)
    {
        $countChoices = 0;
        if (is_array($choices) && count($choices) > 0) {
            foreach ($choices as $choice) {
                if ($choice['catalogNumber'] == $catalogNumber && $choice['className'] == $className) {
                    $countChoices++;
                }
            }
        }

        return $countChoices;
    }

    public function getName()
    {
        return 'export_extension';
    }
}
