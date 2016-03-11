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
     * @param string $specimenCode
     * @return int
     */
    public function getCountDiffs($stats, $className, $specimenCode)
    {
        if (isset($stats['datas'][$specimenCode]['classes'][$className])) {
            return count($stats['datas'][$specimenCode]['classes'][$className]['fields']);
        }
        return 0;
    }

    /**
     * Retourne le nombre des choix par l'utilisateur dans les diffs
     * @param array $choices
     * @param string $className
     * @param string $specimenCode
     * @return integer
     */
    public function getCountChoices($choices, $specimenCode, $className)
    {
        $countChoices = 0;
        if (is_array($choices) && count($choices) > 0) {
            foreach ($choices as $choice) {
                if ($choice['specimenCode'] == $specimenCode && $choice['className'] == $className) {
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
