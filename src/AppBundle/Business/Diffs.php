<?php

namespace AppBundle\Business;


use Symfony\Component\Filesystem\Filesystem;

class Diffs extends \SplFileObject
{
    public $searchDiffs;

    const DIFF_FILENAME = '/diffs.json';
    /**
     * @param string $dirPath
     */
    public function __construct($dirPath)
    {
        $this->searchDiffs = false;
        $path = $dirPath.self::DIFF_FILENAME;
        if (!is_file($path)) {
            $this->searchDiffs = true;
        }
        parent::__construct($path, 'c+');
        chmod($this->getPathname(), 0755);
    }

    /**
     * @param array $diffs
     */
    public function save(array $diffs)
    {
        $fs = new Filesystem();
        if ($fs->exists($this->getPathname())) {

            $responseJson = json_encode($diffs, JSON_PRETTY_PRINT);
            $fs->dumpFile($this->getPathname(), $responseJson);
            chmod($this->getPathname(), 0755);
        }
    }

    /**
     * @return array|mixed
     */
    public function getData()
    {
        $fs = new Filesystem();
        if ($fs->exists($this->getPathname())) {
            $fileContent = json_decode(file_get_contents($this->getPathname()), true);
            return $fileContent;
        }
        return array();
    }

    /**
     * @param string $db
     * @param string|array|null  $selectedClassesNames
     * @return array
     */
    public function getLonesomeRecords($db = null, $selectedClassesNames = null)
    {
        $classesName = [];
        if (!is_null($selectedClassesNames) && is_string($selectedClassesNames) && $selectedClassesNames != 'all') {
            $classesName = [$selectedClassesNames];
            array_walk($classesName, function(&$className) {
                $className = ucfirst(strtolower($className));
            });
        }

        $lonesomeRecords = $this->getData()['lonesomeRecords'];
        $returnLonesomes = [];
        if (!is_null($db)) {
            foreach ($lonesomeRecords as $className => $items) {
                if (!empty($classesName)) {
                    if (in_array($className, $classesName)) {
                        $returnLonesomes[$className][$db] = $lonesomeRecords[$className][$db];
                    }
                } else {
                    $returnLonesomes[$className][$db] = $lonesomeRecords[$className][$db];
                }
            }
        } else {
            if (empty($classesName)) {
                $returnLonesomes = $lonesomeRecords;
            } else {
                foreach ($classesName as $className) {
                    $returnLonesomes[$className] = $lonesomeRecords[$className];
                }
            }
        }
        return $returnLonesomes;
    }

    /**
     * @param string $db
     * @param string $selectedClassName
     * @return array
     */
    public function getLonesomeRecordsIndexedBySpecimenCode($db, $selectedClassName = null)
    {
        $lonesomeRecordsBySpecimenCodes = [];
        $specimenLonesomeRecords = $this->getLonesomeRecords($db, 'specimen');
        $refSpecimenCode = array_column($specimenLonesomeRecords['Specimen'][$db], 'specimenCode');
        $fullLonesomeRecords = $this->getLonesomeRecords($db, $selectedClassName);

        if (!empty($fullLonesomeRecords)) {
            foreach ($fullLonesomeRecords as $className => $lonesomeRecords) {
                foreach ($lonesomeRecords[$db] as $item) {
                    // Si le specimencode de l'enregistrement est dans la liste des specimens de ref c'est que tous les
                    // enregistrements correspondant à ce specimen code sont nouveaux
                    // puisque le specimen n'est pas dans l'autre base
                    if (!in_array($item['specimenCode'], $refSpecimenCode) || $selectedClassName == 'specimen') {
                        $lonesomeRecordsBySpecimenCodes[$item['specimenCode']][] = [
                            'className' => $className,
                            'id' => $item['id']
                        ];
                    } elseif ($selectedClassName == 'all') {
                        $lonesomeRecordsBySpecimenCodes[$item['specimenCode']][] = [
                            'className' => $className,
                            'id' => $item['id']
                        ];
                    }

                }
            }
        }

        return $lonesomeRecordsBySpecimenCodes;
    }


    /**
     * retourne les nouveaux enregistrements pour un specimen code et une base
     * @param string      $specimenCode
     * @param null|string $db
     * @return array
     */
    public function getLonesomeRecordsForSpecimenCode($specimenCode, $db = null)
    {
        if (isset($this->getData()['statsLonesomeRecords'][$specimenCode])) {
            if (is_null($db)) {
                return $this->getData()['statsLonesomeRecords'][$specimenCode];
            } else {
                $lonesomeRecords = $this->getData()['statsLonesomeRecords'][$specimenCode];
                return array_filter($lonesomeRecords, function($el) use ($db) {
                    return $el['db'] == $db;
                });
            }

        }
        return [];
    }

    /**
     * Retourne les nouveaux enregistrements pour une base
     * @param null|string $className
     * @param string      $db
     * @return array
     */
    public function getLonesomeRecordsOrderedBySpecimenCodes($db, $className = null)
    {
        if (!is_null($className)) {
            $className = ucfirst(strtolower($className));
        }
        return array_filter($this->getData()['statsLonesomeRecords'], function($items) use ($db, $className) {
            $itemsFiltered = array_filter($items, function($item) use ($db, $className) {
                if (is_null($className)) {
                    return $item['db'] == $db;
                } else {
                    return $item['db'] == $db && $item['class'] == $className;
                }
            });
            if (count($itemsFiltered) > 0) {
                return true;
            }
            return false;
        });
    }

    /**
     * renvoie les résultats dont au moins une différence fait partie de $classesName
     * @param array $diffs
     * @param array $classesName
     * @return array
     */
    public function filterByClassesName($diffs, array $classesName = [])
    {
        $returnDiffs = $diffs;
        if (count($classesName) > 0) {
            $returnDiffs['classes'] = [];
            $returnDiffs['datas'] = [];
            foreach ($classesName as $className) {
                $className = ucfirst(strtolower($className));
                if (isset($diffs['classes'][$className])) {
                    $returnDiffs['classes'][$className] = $diffs['classes'][$className];
                }
            }
            foreach ($returnDiffs['classes'] as $className => $specimensCode) {
                foreach ($specimensCode as $specimenCode) {
                    if (isset($diffs['datas'][$specimenCode])) {
                        $returnDiffs['datas'][$specimenCode] = $diffs['datas'][$specimenCode];
                        // Rajout dans les classes si un specimen a des modifications dans des classes non sélectionnées
                        foreach (array_keys($returnDiffs['datas'][$specimenCode]['classes']) as $className) {
                            if (!isset($returnDiffs['classes'][$className][$specimenCode])) {
                                $returnDiffs['classes'][$className][] = $specimenCode;
                            }
                        }
                    }
                }
            }
        }
        return $returnDiffs;
    }

    /**
     * renvoie les résultats dont le specimenCode fait partie de $selectedSpecimensCode
     * @param array $diffs
     * @param array $selectedSpecimensCode
     * @return array
     */
    public function filterBySpecimensCode($diffs, array $selectedSpecimensCode = [])
    {
        $returnDiffs = $diffs;
        if (count($selectedSpecimensCode) > 0) {
            // Remise des datas à zero
            $returnDiffs['datas'] = [];
            $returnDiffs['classes'] = $diffs['classes'];
            foreach ($diffs['classes'] as $className => $specimensCode) {
                foreach ($specimensCode as $specimenCode) {
                    if (in_array($specimenCode, $selectedSpecimensCode)) {
                        $returnDiffs['datas'][$specimenCode] = $diffs['datas'][$specimenCode];
                    } else {
                        unset($returnDiffs['classes'][$className][$specimenCode]);
                    }
                }
            }
        }
        return $returnDiffs;
    }

    /**
     * filtre les résultats dont les choix ont été complétement faits
     * @param array $diffs
     * @param array $choicesToRemove
     * @return array
     */
    public function filterByChoicesDone($diffs, array $choicesToRemove = [])
    {
        $returnDiffs = $diffs;
        if (count($choicesToRemove) > 0) {
            $tempChoices = [];
            foreach ($choicesToRemove as $choice) {
                if (!isset($tempChoices[$choice['className']])) {
                    $tempChoices[$choice['className']] = [];
                }
                if (!isset($tempChoices[$choice['className']][$choice['specimenCode']])) {
                    $tempChoices[$choice['className']][$choice['specimenCode']] = 0;
                }
                $tempChoices[$choice['className']][$choice['specimenCode']]++;
            }
            foreach ($tempChoices as $className => $choiceSpecimenCode) {
                foreach ($choiceSpecimenCode as $specimenCode => $comptFieldChoice) {
                    if (isset($returnDiffs['classes'][$className]) && in_array($specimenCode,
                            $returnDiffs['classes'][$className])
                    ) {
                        $totalDiffFields = count($returnDiffs['datas'][$specimenCode]['classes'][$className]['fields']);
                        if ($totalDiffFields == $comptFieldChoice) {
                            if (($key = array_search($specimenCode, $returnDiffs['classes'][$className])) !== false) {
                                unset($returnDiffs['classes'][$className][$key]);
                            }
                            unset($returnDiffs['datas'][$specimenCode]['classes'][$className]);
                            if (isset($returnDiffs['datas'][$specimenCode]) && count($returnDiffs['datas'][$specimenCode]['classes']) == 0) {
                                unset($returnDiffs['datas'][$specimenCode]);
                            }
                        }
                    }
                }
            }
        }
        return $returnDiffs;
    }

    /**
     * filtre les résultats
     * @param array $diffs
     * @param array $classesName
     * @param array $selectedSpecimensCode
     * @param array $choicesToRemove
     * @return array
     */
    public function filterResults(
        $diffs,
        array $classesName = [],
        array $selectedSpecimensCode = [],
        array $choicesToRemove = []
    ) {
        $returnDiffs = $this->filterByClassesName($diffs, $classesName);
        $returnDiffs = $this->filterBySpecimensCode($returnDiffs, $selectedSpecimensCode);
        $returnDiffs = $this->filterByChoicesDone($returnDiffs, $choicesToRemove);
        return $returnDiffs;
    }


    public function deleteChoices()
    {
        parent::__construct($this->getPathname(), 'w+');
        parent::__construct($this->getPathname(), 'c+');
        $this->searchDiffs = true;
    }
}
