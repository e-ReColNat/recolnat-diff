<?php

namespace AppBundle\Business;

use AppBundle\Manager\UtilityService;

class Diffs extends AbstractFile
{
    public $searchDiffs;
    const FILENAME = '/diffs.json';

    /**
     * @param string $dirPath
     * @param string $userGroup
     */
    public function __construct($dirPath, $userGroup)
    {
        $this->searchDiffs = false;
        $path = UtilityService::createFile($dirPath.self::FILENAME, $userGroup);
        parent::__construct($path, 'c+');
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
                        foreach (array_keys($diffs['datas'][$catalogNumber]) as $className) {
                            if (!isset($returnDiffs['classes'][$className][$catalogNumber])) {
                                $returnDiffs['classes'][$className][$catalogNumber] =
                                    $diffs['datas'][$catalogNumber][$className]['fields'];
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
                        $totalDiffFields = count($returnDiffs['datas'][$catalogNumber][$className]['fields']);
                        if ($totalDiffFields == $comptFieldChoice) {
                            if (($key = array_search($catalogNumber, $returnDiffs['classes'][$className])) !== false) {
                                unset($returnDiffs['classes'][$className][$key]);
                            }
                            unset($returnDiffs['datas'][$catalogNumber][$className]);
                            if (isset($returnDiffs['datas'][$catalogNumber]) &&
                                count($returnDiffs['datas'][$catalogNumber]) == 0
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
