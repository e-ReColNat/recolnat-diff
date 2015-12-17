<?php

namespace AppBundle\Business;

/**
 * Description of Diff
 *
 * @author tpateffoz
 */
class Diffs extends \SplFileObject
{
    public $generateDiff ;
    public function __construct($dirPath)
    {
        $this->generateDiff=false;
        $path = $dirPath.'/diffs.json';
        if (!is_file($path)) {
            $this->generateDiff=true;
        }
        parent::__construct($path, 'c+');
        chmod($this->getPathname(), 0755);
    }
    
    public function saveDiffs(array $diffs, array $stats, array $specimensCode) {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        if ($fs->exists($this->getPathname())) {
            
            $responseJson = json_encode(
                    [
                    'specimensCode' => $specimensCode,
                    'stats' => $stats, 
                    'diffs' => $diffs
                    ]
                    , JSON_PRETTY_PRINT);
            $fs->dumpFile($this->getPathname(), $responseJson);
            chmod($this->getPathname(), 0755);
        }
    }
    
    public function getData() {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        if ($fs->exists($this->getPathname())) {
            $fileContent=json_decode(file_get_contents($this->getPathname()),true);
            $specimensCode = $fileContent['specimensCode'];
            $diffs = $fileContent['diffs'];
            $stats = $fileContent['stats'];
            return array(
            'specimensCode' => $specimensCode,
            'stats' => $stats, 
            'diffs' => $diffs) ;
        }
        return array(
            'specimensCode' => [],
            'stats' => [], 
            'diffs' => []) ;
    }
    /**
     * renvoie les résultats dont au moins une différence fait partie de $classesName
     * @param array $stats
     * @param array $classesName
     * @return array
     */
    public function filterByClassesName($stats, array $classesName=[]) {
        $returnStats=$stats;
        
        if (count($classesName)>0) {
            $returnStats['classes']=[] ;
            $returnStats['summary'] = [];
            foreach ($classesName as $className) {
                $className = ucfirst(strtolower($className));
                if (isset($stats['classes'][$className])) {
                    $returnStats['classes'][$className] = $stats['classes'][$className] ;
                }
            }
            foreach ($returnStats['classes'] as $className => $row) {
                foreach ($row as $specimenCode => $fields) {
                    if (isset($stats['summary'][$specimenCode])) {
                        $returnStats['summary'][$specimenCode] = $stats['summary'][$specimenCode] ;
                        // Rajout dans les classes si un specimen a des mofifications dans des class non sélectionnées
                        foreach (array_keys($returnStats['summary'][$specimenCode]) as $className) {
                            if (!isset($returnStats['classes'][$className][$specimenCode])) {
                                $returnStats['classes'][$className][$specimenCode] = $stats['classes'][$className][$specimenCode] ;
                            }
                        }
                    }
                }
            }
        }
        
        return $returnStats ;
    }
    
    /**
     * renvoie les résultats dont le specimenCode fait partie de $selectedSpecimensCode
     * @param array $stats
     * @param array $selectedSpecimensCode
     * @return array
     */
    public function filterBySpecimensCode($stats,array $selectedSpecimensCode=[]) {
        $returnStats=$stats;
        if (count($selectedSpecimensCode)>0) {
            // Remise du summary à zero
            $returnStats['summary']=[];
            $returnStats['classes']=$stats['classes'];
            foreach ($stats['classes'] as $className => $row) {
                foreach ($row as $specimenCode => $fields) {
                    if (in_array($specimenCode, $selectedSpecimensCode)) {
                        $returnStats['summary'][$specimenCode] = $stats['summary'][$specimenCode] ;
                    }
                    else {
                        unset($returnStats['classes'][$className][$specimenCode]);
                    }
                }
            }
        }
        return $returnStats;
    }
    
    /**
     * filtre les résultats dont les choix ont été complétement faits
     * @param type $stats
     * @param array $choicesToRemove
     * @return type
     */
    public function filterByChoicesDone($stats, array $choicesToRemove=[]) {
        $returnStats=$stats;
        if (count($choicesToRemove) >0) {
            $tempChoices=[] ;
            foreach ($choicesToRemove as $choice) {
                if (!isset($tempChoices[$choice['className']])) {
                    $tempChoices[$choice['className']]=[];
                }
                if (!isset($tempChoices[$choice['className']][$choice['specimenId']])) {
                    $tempChoices[$choice['className']][$choice['specimenId']]=0;
                }
                $tempChoices[$choice['className']][$choice['specimenId']]++;
            }
            foreach ($tempChoices as $className => $choiceSpecimenId) {
                foreach ($choiceSpecimenId as $specimenCode => $comptFieldChoice) {
                    if (isset($returnStats['classes'][$className]) && isset($returnStats['classes'][$className][$specimenCode])) {
                        $totalStatFields=0;
                        foreach ($returnStats['classes'][$className][$specimenCode] as $statsFields) {
                            $totalStatFields+=count($statsFields) ;
                        }
                        if ($totalStatFields == $comptFieldChoice) {
                            unset($returnStats['classes'][$className][$specimenCode]) ;
                            unset($returnStats['summary'][$specimenCode][$className]) ;
                            if (isset($returnStats['summary'][$specimenCode]) && count($returnStats['summary'][$specimenCode]) == 0) {
                                unset($returnStats['summary'][$specimenCode]);
                            }
                        }
                    }
                }
            }
        }
        return $returnStats ;
    }
    
    /**
     * filtre les résultats
     * @param array $stats
     * @param array $classesName
     * @param array $selectedSpecimensCode
     * @param array $choicesToRemove
     * @return array
     */
    public function filterResults($stats, array $classesName=[], array $selectedSpecimensCode=[], array $choicesToRemove=[]) {
        $returnStats = $this->filterByClassesName($stats, $classesName) ;
        $returnStats = $this->filterBySpecimensCode($returnStats, $selectedSpecimensCode);
        $returnStats = $this->filterByChoicesDone($returnStats, $choicesToRemove);
        return $returnStats;
    }
    
}
