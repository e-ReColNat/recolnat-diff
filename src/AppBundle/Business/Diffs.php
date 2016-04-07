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
     * @param string|null       $db
     * @param string|array|null $selectedClassesNames
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
     * @param string      $db
     * @param string|null $selectedClassName
     * @return array
     */
    public function getLonesomeRecordsIndexedByCatalogNumber($db, $selectedClassName = null)
    {
        $lonesomeRecordsByCatalogNumbers = [];
        $specimenLonesomeRecords = $this->getLonesomeRecords($db, 'specimen');
        $refCatalogNumber = array_column($specimenLonesomeRecords['Specimen'][$db], 'catalogNumber');
        $fullLonesomeRecords = $this->getLonesomeRecords($db, $selectedClassName);

        if (!empty($fullLonesomeRecords)) {
            foreach ($fullLonesomeRecords as $className => $lonesomeRecords) {
                foreach ($lonesomeRecords[$db] as $item) {
                    // Si le catalogNumber de l'enregistrement est dans la liste des specimens de ref c'est que tous les
                    // enregistrements correspondant à ce specimen code sont nouveaux
                    // puisque le specimen n'est pas dans l'autre base
                    if (!in_array($item['code'], $refCatalogNumber) || $selectedClassName == 'specimen') {
                        $lonesomeRecordsByCatalogNumbers[$item['code']][] = [
                            'className' => $className,
                            'id' => $item['id']
                        ];
                    } elseif ($selectedClassName == 'all') {
                        $lonesomeRecordsByCatalogNumbers[$item['code']][] = [
                            'className' => $className,
                            'id' => $item['id']
                        ];
                    }

                }
            }
        }

        return $lonesomeRecordsByCatalogNumbers;
    }


    /**
     * retourne les nouveaux enregistrements pour un specimen code et une base
     * @param string      $catalogNumber
     * @param null|string $db
     * @return array
     */
    public function getLonesomeRecordsForCatalogNumber($catalogNumber, $db = null)
    {
        if (isset($this->getData()['statsLonesomeRecords'][$catalogNumber])) {
            if (is_null($db)) {
                return $this->getData()['statsLonesomeRecords'][$catalogNumber];
            } else {
                $lonesomeRecords = $this->getData()['statsLonesomeRecords'][$catalogNumber];

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
    public function getLonesomeRecordsOrderedByCatalogNumbers($db, $className = null)
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

            return count($itemsFiltered) > 0;
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
            foreach ($returnDiffs['classes'] as $className => $catalogNumbers) {
                foreach ($catalogNumbers as $catalogNumber) {
                    if (isset($diffs['datas'][$catalogNumber])) {
                        $returnDiffs['datas'][$catalogNumber] = $diffs['datas'][$catalogNumber];
                        // Rajout dans les classes si un specimen a des modifications dans des classes non sélectionnées
                        foreach (array_keys($diffs['datas'][$catalogNumber]['classes']) as $className) {
                            if (!isset($returnDiffs['classes'][$className][$catalogNumber])) {
                                $returnDiffs['classes'][$className][$catalogNumber] =
                                    $diffs['datas'][$catalogNumber]['classes'][$className]['fields'];
                            }
                        }
                    }
                }
            }
        }

        return $returnDiffs;
    }

    /**
     * renvoie les résultats dont le catalogNumber fait partie de $selectedCatalogNumbers
     * @param array $diffs
     * @param array $selectedCatalogNumbers
     * @return array
     */
    public function filterByCatalogNumbers($diffs, array $selectedCatalogNumbers = [])
    {
        $returnDiffs = $diffs;
        if (count($selectedCatalogNumbers) > 0) {
            // Remise des datas à zero
            $returnDiffs['datas'] = [];
            $returnDiffs['classes'] = [];

            foreach ($selectedCatalogNumbers as $selectedCatalogNumber) {
                if (array_key_exists($selectedCatalogNumber, $diffs['datas'])) {
                    $returnDiffs['datas'][$selectedCatalogNumber] = $diffs['datas'][$selectedCatalogNumber];
                    foreach (array_keys($diffs['datas'][$selectedCatalogNumber]) as $className) {
                        $className = ucfirst($className);
                        if (!isset($returnDiffs['classes'][$className])) {
                            $returnDiffs['classes'][$className] = [];
                        }
                        $returnDiffs['classes'][$className][] = $selectedCatalogNumber;
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
                if (!isset($tempChoices[$choice['className']][$choice['catalogNumber']])) {
                    $tempChoices[$choice['className']][$choice['catalogNumber']] = 0;
                }
                $tempChoices[$choice['className']][$choice['catalogNumber']]++;
            }
            foreach ($tempChoices as $className => $choiceCatalogNumber) {
                foreach ($choiceCatalogNumber as $catalogNumber => $comptFieldChoice) {
                    if (isset($returnDiffs['classes'][$className]) && isset($returnDiffs['classes'][$className][$catalogNumber])
                    ) {
                        $totalDiffFields = count($returnDiffs['datas'][$catalogNumber]['classes'][$className]['fields']);
                        if ($totalDiffFields == $comptFieldChoice) {
                            if (($key = array_search($catalogNumber, $returnDiffs['classes'][$className])) !== false) {
                                unset($returnDiffs['classes'][$className][$key]);
                            }
                            unset($returnDiffs['datas'][$catalogNumber]['classes'][$className]);
                            if (isset($returnDiffs['datas'][$catalogNumber]) &&
                                count($returnDiffs['datas'][$catalogNumber]['classes']) == 0
                            ) {
                                unset($returnDiffs['datas'][$catalogNumber]);
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
     * @param array $selectedCatalogNumbers
     * @param array $choicesToRemove
     * @return array
     */
    public function filterResults(
        $diffs,
        array $classesName = [],
        array $selectedCatalogNumbers = [],
        array $choicesToRemove = []
    ) {
        $returnDiffs = $this->filterByClassesName($diffs, $classesName);
        $returnDiffs = $this->filterByCatalogNumbers($returnDiffs, $selectedCatalogNumbers);
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
