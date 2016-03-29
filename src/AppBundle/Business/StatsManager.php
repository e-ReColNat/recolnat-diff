<?php

namespace AppBundle\Business;


use AppBundle\Business\User\User;
use AppBundle\Manager\ExportManager;

class StatsManager
{
    /**
     * @var ExportManager
     */
    public $exportManager;

    private $collectionCode;
    private $user;

    public function __construct(ExportManager $exportManager)
    {
        $this->exportManager = $exportManager;
    }

    /**
     * @param User $user
     * @param string $collectionCode
     * @return $this
     */
    public function init(User $user, $collectionCode)
    {
        $this->user = $user;
        $this->collectionCode = $collectionCode;
        $this->exportManager = $this->exportManager->init($this->user)->setCollectionCode($this->collectionCode);
        return $this;
    }

    /**
     * @return array
     */
    public function getStatsLonesomeRecords()
    {
        $lonesomeRecords = $this->exportManager->getDiffHandler()->getLoneSomeRecords();
        $stats = [];
        if (is_array($lonesomeRecords)) {
            foreach ($lonesomeRecords as $className => $items) {
                $stats[$className]['recolnat'] = isset($items['recolnat']) ? count($items['recolnat']) : 0;
                $stats[$className]['institution'] = isset($items['institution']) ? count($items['institution']) : 0;
            }
        }
        return $stats;
    }


    /**
     * @return array
     */
    public function getSumLonesomeRecords()
    {
        $statsLonesomeRecords = $this->getStatsLonesomeRecords();
        $sumLonesomeRecords = ['recolnat' => 0, 'institution' => 0];
        foreach ($statsLonesomeRecords as $lonesomeRecords) {
            $sumLonesomeRecords['recolnat'] += $lonesomeRecords['recolnat'];
            $sumLonesomeRecords['institution'] += $lonesomeRecords['institution'];
        }
        return $sumLonesomeRecords;
    }


    /**
     * @return array
     */
    public function getStatsChoices()
    {
        $choices = $this->exportManager->getSessionHandler()->getChoicesForDisplay();

        $statsChoices = [];
        $callbackCountChoices = function($value, $className) use (&$statsChoices) {
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
        if (is_array($this->exportManager->sessionManager->get('stats'))) {
            return $this->exportManager->sessionManager->get('stats');
        }
        return [];
    }

    /**
     * @return array
     */
    public function getSumStats()
    {
        $stats = $this->getExpandedStats();
        $sumStats = ['specimens' => 0, 'diffs' => 0, 'fields' => 0];
        foreach ($stats as $datas) {
            $sumStats['specimens'] += $datas['specimens'];
            $sumStats['diffs'] += $datas['diffs'];
            $sumStats['fields'] += count($datas['fields']);
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
        $diffs = $this->exportManager->getDiffs();
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
     * @param array  $classesName
     * @param string $dateFormat
     * @return array
     */
    public function getStatsBySimilarity($classesName = [], $dateFormat = 'd/M/Y')
    {
        $diffs = $this->exportManager->sessionManager->get('diffs');
        if (empty($classesName)) {
            $classesName = array_keys($diffs['classes']);
        }
        array_map(function($value) {
            return ucfirst(strtolower($value));
        }, $classesName);

        $dataSeparator = '\#|#/';
        $stats = [];
        foreach ($classesName as $className) {
            if (isset($diffs['classes'][$className]) && !empty($diffs['classes'][$className])) {
                foreach ($diffs['classes'][$className] as $specimenCode=>$datas) {
                    if (isset($diffs['datas'][$specimenCode])) {
                        $details = $diffs['datas'][$specimenCode]['classes'][$className];
                        $taxon = $diffs['datas'][$specimenCode]['taxon'];
                        foreach ($details['fields'] as $fieldName => $datas) {
                            // Traitement des dates
                            if (is_array($datas['recolnat']) && isset($datas['recolnat']['date'])) {
                                $date = new \DateTime($datas['recolnat']['date']);
                                $datas['recolnat'] = $date->format($dateFormat);
                            }
                            if (is_array($datas['institution']) && isset($datas['institution']['date'])) {
                                $date = new \DateTime($datas['institution']['date']);
                                $datas['institution'] = $date->format($dateFormat);
                            }
                            // Création d'une clé unique
                            $concatDatas = md5(implode($dataSeparator,
                                [$className, $fieldName, $datas['recolnat'], $datas['institution']]));

                            if (!isset($stats[$concatDatas])) {
                                $stats[$concatDatas] = ['taxons' => [], 'specimensCode' => []];
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
        uasort($stats, function($a, $b) {
            $a = count($a['specimensCode']);
            $b = count($b['specimensCode']);
            return ($a == $b) ? 0 : (($a > $b) ? -1 : 1);
        });
        return $stats;
    }

    /**
     * @return array
     */
    public function getCondensedStats()
    {
        $stats = [];
        foreach ($this->getStats() as $className => $fields) {
            $stats[$className] = array_sum($fields);
        }
        return $stats;
    }

    /**
     * @return mixed
     */
    public function getSortedStats()
    {
        $functionSortStats = function($a, $b) {
            if ($a['diffs'] == $b['diffs']) {
                return 0;
            }

            return ($a['diffs'] > $b['diffs']) ? -1 : 1;
        };
        $stats = $this->getExpandedStats();
        uasort($stats, $functionSortStats);
        return $stats;
    }
}
