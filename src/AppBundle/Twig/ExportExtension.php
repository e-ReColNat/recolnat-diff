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
        );
    }
    
    /**
     * Retourne le nombre de différence trouvées entre les deux bases pour un couple Spécimen / Class
     * @param array $stats
     * @param string $className
     * @param string $specimenId
     * @return int
     */
    public function getCountDiffs($stats, $className, $specimenId)
    {
        if (isset($stats['summary'][$specimenId][$className])) {
            return $stats['summary'][$specimenId][$className]['fields'];
        }
        return 0;
    }
    /**
     * Retourne le nombre des choix par l'utilisateur dans les diffs
     * @param array $choices
     * @param string $className
     * @param string $specimenId
     * @return type
     */
    public function getCountChoices($choices, $specimenId, $className)
    {
        $countChoices = 0 ;
        if (is_array($choices) && count($choices)>0) {
            foreach ($choices as $choice) {
                if ($choice['specimenId'] == $specimenId && $choice['className'] == $className) {
                    $countChoices++;
                }
            }
        }
        /*if (is_array($stats['classes'][$className][$specimenId])) {
            foreach($stats['classes'][$className][$specimenId] as $relationId => $row) {
                $fieldName = key($row) ;
                foreach ($choices as $choice) {
                    if (
                        $choice['className'] == $className &&
                        $choice['fieldName'] == $fieldName &&
                        $choice['relationId'] == $relationId 
                        ) {
                        $countChoices++;
                    }
                }
            }
        }*/
        return $countChoices;
    }
    public function getName()
    {
        return 'export_extension';
    }
}