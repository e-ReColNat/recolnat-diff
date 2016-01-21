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
    
    public function save(array $diffs) {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        if ($fs->exists($this->getPathname())) {
            
            $responseJson = json_encode($diffs, JSON_PRETTY_PRINT);
            $fs->dumpFile($this->getPathname(), $responseJson);
            chmod($this->getPathname(), 0755);
        }
    }
    
    public function getData() {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        if ($fs->exists($this->getPathname())) {
            $fileContent=json_decode(file_get_contents($this->getPathname()),true);
            return $fileContent ;
        }
        return array(
            ) ;
    }
    /**
     * renvoie les résultats dont au moins une différence fait partie de $classesName
     * @param array $diffs
     * @param array $classesName
     * @return array
     */
    public function filterByClassesName($diffs, array $classesName=[]) {
        $returnStats=$diffs;
        if (count($classesName)>0) {
            $returnStats['classes']=[] ;
            $returnStats['datas'] = [];
            foreach ($classesName as $className) {
                $className = ucfirst(strtolower($className));
                if (isset($diffs['classes'][$className])) {
                    $returnStats['classes'][$className] = $diffs['classes'][$className] ;
                }
            }
            foreach ($returnStats['classes'] as $className => $specimensCode) {
                foreach ($specimensCode as $specimenCode) {
                    if (isset($diffs['datas'][$specimenCode])) {
                        $returnStats['datas'][$specimenCode] = $diffs['datas'][$specimenCode] ;
                        // Rajout dans les classes si un specimen a des modifications dans des classes non sélectionnées
                        foreach (array_keys($returnStats['datas'][$specimenCode]['classes']) as $className) {
                            if (!isset($returnStats['classes'][$className][$specimenCode])) {
                                $returnStats['classes'][$className][] = $specimenCode ;
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
     * @param array $diffs
     * @param array $selectedSpecimensCode
     * @return array
     */
    public function filterBySpecimensCode($diffs,array $selectedSpecimensCode=[]) {
        $returnStats=$diffs;
        if (count($selectedSpecimensCode)>0) {
            // Remise du datas à zero
            $returnStats['datas']=[];
            $returnStats['classes']=$diffs['classes'];
            foreach ($diffs['classes'] as $className => $specimensCode) {
                foreach ($specimensCode as $specimenCode) {
                    if (in_array($specimenCode, $selectedSpecimensCode)) {
                        $returnStats['datas'][$specimenCode] = $diffs['datas'][$specimenCode] ;
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
     * @param type $diffs
     * @param array $choicesToRemove
     * @return type
     */
    public function filterByChoicesDone($diffs, array $choicesToRemove=[]) {
        $returnStats=$diffs;
        if (count($choicesToRemove) >0) {
            $tempChoices=[] ;
            foreach ($choicesToRemove as $choice) {
                if (!isset($tempChoices[$choice['className']])) {
                    $tempChoices[$choice['className']]=[];
                }
                if (!isset($tempChoices[$choice['className']][$choice['specimenCode']])) {
                    $tempChoices[$choice['className']][$choice['specimenCode']]=0;
                }
                $tempChoices[$choice['className']][$choice['specimenCode']]++;
            }
            foreach ($tempChoices as $className => $choiceSpecimenCode) {
                foreach ($choiceSpecimenCode as $specimenCode => $comptFieldChoice) {
                    if (isset($returnStats['classes'][$className]) && isset($returnStats['classes'][$className][$specimenCode])) {
                        $totalStatFields=0;
                        foreach ($returnStats['classes'][$className][$specimenCode] as $statsFields) {
                            $totalStatFields+=count($statsFields) ;
                        }
                        if ($totalStatFields == $comptFieldChoice) {
                            unset($returnStats['classes'][$className][$specimenCode]) ;
                            unset($returnStats['datas'][$specimenCode][$className]) ;
                            if (isset($returnStats['datas'][$specimenCode]) && count($returnStats['datas'][$specimenCode]) == 0) {
                                unset($returnStats['datas'][$specimenCode]);
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
    
    public function deleteChoices()
    {
        parent::__construct($this->getPathname(), 'w+');
        parent::__construct($this->getPathname(), 'c+');
        $this->generateDiff=true;
    }
}
