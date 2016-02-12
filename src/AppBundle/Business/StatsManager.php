<?php
/**
 * Created by PhpStorm.
 * User: tpateffoz
 * Date: 11/02/16
 * Time: 14:59
 */

namespace AppBundle\Business;


use AppBundle\Manager\ExportManager;

class StatsManager
{
    /**
     * @var ExportManager
     */
    public $exportManager;

    private $institutionCode;
    private $collectionCode;

    public function __construct(ExportManager $exportManager)
    {
        $this->exportManager = $exportManager;
    }

    /**
     * @param string $institutionCode
     * @param string $collectionCode
     * @return $this
     */
    public function init($institutionCode, $collectionCode)
    {
        $this->institutionCode = $institutionCode;
        $this->collectionCode = $collectionCode;
        $this->exportManager = $this->exportManager->init($institutionCode, $collectionCode);
        return $this;
    }

    /**
     * @return array
     */
    public function getStatsLonesomeRecords() {
        $lonesomeRecords = $this->exportManager->getLoneSomeRecords() ;
        $stats = [];
        $refRecolnatSpecimenCode = array_column($lonesomeRecords['Specimen']['recolnat'], 'specimenCode') ;
        $refInstitutionSpecimenCode = array_column($lonesomeRecords['Specimen']['institution'], 'specimenCode') ;
        foreach ($lonesomeRecords as $className => $items) {
            // si la className n'est pas specimen et que l'enregistrement est déjà présent dans les
            // spécimens alors on a affaire à un nouveau specimen donc on l'enlève du décompte
            if ($className !== 'Specimen') {
                $specimenCodes = array_column($items['recolnat'], 'specimenCode');
                $stats[$className]['recolnat'] = count(array_diff($specimenCodes, $refRecolnatSpecimenCode));
                $specimenCodes = array_column($items['institution'], 'specimenCode');
                $stats[$className]['institution'] = count(array_diff($specimenCodes, $refInstitutionSpecimenCode));
            }
            else {
                $stats[$className]['recolnat'] = count($items['recolnat']);
                $stats[$className]['institution'] = count($items['institution']);
            }
        }
        return $stats;
    }


    /**
     * @return array
     */
    public function getSumLonesomeRecords() {
        $statsLonesomeRecords=$this->getStatsLonesomeRecords();
        $sumLonesomeRecords=['recolnat'=>0, 'institution'=>0];
        foreach ($statsLonesomeRecords as $lonesomeRecords) {
            $sumLonesomeRecords['recolnat']+=$lonesomeRecords['recolnat'];
            $sumLonesomeRecords['institution']+=$lonesomeRecords['institution'];
        }
        return $sumLonesomeRecords;
    }


    /**
     * @return array
     */
    public function getStatsChoices()
    {
        $choices = $this->exportManager->getChoicesForDisplay();

        $statsChoices = [];
        $callbackCountChoices = function ($value, $className) use (&$statsChoices) {
            if (is_array($value)) {
                if (!isset($statsChoices[$className])) {
                    $statsChoices[$className] = 0;
                }
                foreach ($value as $row) {
                    foreach ($row as $fields) {
                        $statsChoices[$className] += count($fields);
                    }
                }
            }
        };

        array_walk($choices, $callbackCountChoices);
        $statsChoices['sum'] = array_sum($statsChoices);
        return $statsChoices;
    }

    /**
     * @return mixed
     */
    public function getStats()
    {
        return $this->exportManager->sessionManager->get('stats');
    }

    /**
     * @return array
     */
    public function getSumStats()
    {
        $stats = $this->getExpandedStats();
        $sumStats = ['specimens' => 0, 'diffs' => 0, 'fields' => 0];
        foreach ($stats as $datas) {
            $sumStats['specimens']+=$datas['specimens'];
            $sumStats['diffs']+=$datas['diffs'];
            $sumStats['fields']+=count($datas['fields']);
        }
        return $sumStats;
    }

    /**
     * Renvoie les stats des diffs avec les données
     * @param string $order
     * @return array
     */
    public function getExpandedStats($order = 'desc')
    {
        $stats = [];
        $diffs = $this->exportManager->sessionManager->get('diffs');
        foreach ($this->getStats() as $className => $fields) {
            $stats[$className]['diffs'] = array_sum($fields);
            $tempFields = $fields;
            switch ($order) {
                case 'desc':
                    arsort($tempFields);
                    break;
                case 'asc':
                    asort($tempFields);
                    break;
            }
            $stats[$className]['fields'] = $tempFields;
            $stats[$className]['specimens'] = count($diffs['classes'][$className]);
        }
        return $stats;
    }

    /**
     * Renvoie les statistiques de diffs présentant les mêmes données modifiées pour des champs identiques
     * @param array $classesName
     * @param string $dateFormat
     * @return array
     */
    public function getStatsBySimilarity($classesName = [], $dateFormat ='d/M/Y')
    {
        $diffs = $this->exportManager->sessionManager->get('diffs');
        if (empty($classesName)) {
            $classesName = array_keys($diffs['classes']) ;
        }
        array_map(function($value) {
            return ucfirst(strtolower($value)) ;
        }, $classesName) ;

        $dataSeparator = '\#|#/';
        $stats = [];
        foreach ($classesName as $className) {
            if (isset($diffs['classes'][$className]) && !empty($diffs['classes'][$className])) {
                foreach ($diffs['classes'][$className] as $specimenCode) {
                    if (isset($diffs['datas'][$specimenCode])) {
                        $details = $diffs['datas'][$specimenCode]['classes'][$className] ;
                        $taxon = $diffs['datas'][$specimenCode]['taxon'] ;
                        foreach ($details['fields'] as $fieldName => $datas) {
                            // Traitement des dates
                            if (is_array($datas['recolnat']) && isset($datas['recolnat']['date'])) {
                                $date = new \DateTime($datas['recolnat']['date']) ;
                                $datas['recolnat'] = $date->format($dateFormat)  ;
                            }
                            if (is_array($datas['institution']) && isset($datas['institution']['date'])) {
                                $date = new \DateTime($datas['institution']['date']) ;
                                $datas['institution'] = $date->format($dateFormat)  ;
                            }
                            // Création d'une clé unique
                            $concatDatas = md5(implode($dataSeparator, [$className, $fieldName, $datas['recolnat'], $datas['institution']])) ;

                            if (!isset($stats[$concatDatas])) {
                                $stats[$concatDatas] = ['taxons'=>[], 'specimensCode'=>[]];
                            }

                            $stats[$concatDatas]['specimensCode'][$specimenCode] = $details['id'];
                            $stats[$concatDatas]['taxons'][$specimenCode] = $taxon;
                            $stats[$concatDatas]['datas'] = $datas;
                            $stats[$concatDatas]['className'] = $className;
                            $stats[$concatDatas]['fieldName'] = $fieldName;
                        }
                    }
                }
            }
        }
        uasort($stats, function ($a, $b) {
            $a = count($a['specimensCode']);
            $b = count($b['specimensCode']);
            return ($a == $b) ? 0 : (($a > $b) ? -1 : 1);
        });
        return $stats;
    }

    public function getCondensedStats()
    {
        $stats = [];
        foreach ($this->getStats() as $className => $fields) {
            $stats[$className] = array_sum($fields);
        }
        return $stats;
    }
}