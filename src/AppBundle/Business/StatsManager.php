<?php

namespace AppBundle\Business;


use AppBundle\Business\User\User;
use AppBundle\Manager\AbstractDiff;
use AppBundle\Manager\DiffManager;
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
     * @param User   $user
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

        $stats = $this->setEmptyArrayStatsLonesomeRecords();
        if (is_array($lonesomeRecords)) {
            foreach ($lonesomeRecords as $catalogNumber => $itemsPerClassName) {
                foreach ($itemsPerClassName as $className => $items) {
                    foreach($items as $item) {
                        $stats[$className][$item['db']]++;
                    }
                }
            }
        }

        return $stats;
    }

    private function setEmptyArrayStatsLonesomeRecords()
    {
        $emptyArray = [];
        foreach (DiffManager::ENTITIES_NAME as $entityName) {
            $emptyArray[$entityName] = [AbstractDiff::KEY_RECOLNAT => 0, AbstractDiff::KEY_INSTITUTION => 0];
        }

        return $emptyArray;
    }

    /**
     * @return array
     */
    public function getSumLonesomeRecords()
    {
        $statsLonesomeRecords = $this->getStatsLonesomeRecords();
        $sumLonesomeRecords = [AbstractDiff::KEY_RECOLNAT => 0, AbstractDiff::KEY_INSTITUTION => 0];
        foreach ($statsLonesomeRecords as $lonesomeRecords) {
            $sumLonesomeRecords[AbstractDiff::KEY_RECOLNAT] += $lonesomeRecords[AbstractDiff::KEY_RECOLNAT];
            $sumLonesomeRecords[AbstractDiff::KEY_INSTITUTION] += $lonesomeRecords[AbstractDiff::KEY_INSTITUTION];
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
                    $statsChoices[$className] += count($row);
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
        /*if (is_array($this->exportManager->sessionManager->get('stats'))) {
            return $this->exportManager->sessionManager->get('stats');
        }
        return [];*/
        return $this->exportManager->getDiffHandler()->getStatsFile()->getData();
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
        $expandedStats = [];
        $fullStats = $this->getStats();
        foreach ($fullStats['stats'] as $className => $fields) {
            $expandedStats[$className]['diffs'] = array_sum($fields);
            $tempFields = $fields;
            switch ($order) {
                case 'desc':
                    arsort($tempFields);
                    break;
                case 'asc':
                    asort($tempFields);
                    break;
            }
            $expandedStats[$className]['fields'] = $tempFields;
            $expandedStats[$className]['specimens'] = count($fullStats['classes'][$className]);
        }

        return $expandedStats;
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
        $catalogNumbers = [];
        foreach ($classesName as $className) {
            if (isset($diffs['classes'][$className]) && !empty($diffs['classes'][$className])) {
                foreach ($diffs['classes'][$className] as $catalogNumber) {
                    if (isset($diffs['datas'][$catalogNumber])) {
                        $details = $diffs['datas'][$catalogNumber][$className];
                        foreach ($details['fields'] as $fieldName => $datas) {
                            // Traitement des dates
                            if (is_array($datas[AbstractDiff::KEY_RECOLNAT]) && isset($datas[AbstractDiff::KEY_RECOLNAT]['date'])) {
                                $date = new \DateTime($datas[AbstractDiff::KEY_RECOLNAT]['date']);
                                $datas[AbstractDiff::KEY_RECOLNAT] = $date->format($dateFormat);
                            }
                            if (is_array($datas[AbstractDiff::KEY_INSTITUTION]) && isset($datas[AbstractDiff::KEY_INSTITUTION]['date'])) {
                                $date = new \DateTime($datas[AbstractDiff::KEY_INSTITUTION]['date']);
                                $datas['institution'] = $date->format($dateFormat);
                            }
                            // Création d'une clé unique
                            $concatDatas = md5(implode($dataSeparator,
                                [
                                    $className,
                                    $fieldName,
                                    $datas[AbstractDiff::KEY_RECOLNAT],
                                    $datas[AbstractDiff::KEY_INSTITUTION]
                                ]));

                            if (!isset($stats[$concatDatas])) {
                                $stats[$concatDatas] = ['taxons' => [], 'catalogNumbers' => []];
                            }

                            $stats[$concatDatas]['catalogNumbers'][$catalogNumber] = $details['id'];
                            $stats[$concatDatas]['datas'] = $datas;
                            $stats[$concatDatas]['className'] = $className;
                            $stats[$concatDatas]['fieldName'] = $fieldName;
                            $catalogNumbers[] = $catalogNumber;
                        }
                    }
                }
            }
        }
        uasort($stats, function($a, $b) {
            $a = count($a['catalogNumbers']);
            $b = count($b['catalogNumbers']);

            return ($a == $b) ? 0 : (($a > $b) ? -1 : 1);
        });

        return [$stats, $catalogNumbers];
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
