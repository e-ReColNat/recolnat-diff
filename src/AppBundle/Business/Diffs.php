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
        $returnDiffs=$diffs;
        if (count($classesName)>0) {
            $returnDiffs['classes']=[] ;
            $returnDiffs['datas'] = [];
            foreach ($classesName as $className) {
                $className = ucfirst(strtolower($className));
                if (isset($diffs['classes'][$className])) {
                    $returnDiffs['classes'][$className] = $diffs['classes'][$className] ;
                }
            }
            foreach ($returnDiffs['classes'] as $className => $specimensCode) {
                foreach ($specimensCode as $specimenCode) {
                    if (isset($diffs['datas'][$specimenCode])) {
                        $returnDiffs['datas'][$specimenCode] = $diffs['datas'][$specimenCode] ;
                        // Rajout dans les classes si un specimen a des modifications dans des classes non sélectionnées
                        foreach (array_keys($returnDiffs['datas'][$specimenCode]['classes']) as $className) {
                            if (!isset($returnDiffs['classes'][$className][$specimenCode])) {
                                $returnDiffs['classes'][$className][] = $specimenCode ;
                            }
                        }
                    }
                }
            }
        }
        return $returnDiffs ;
    }
    
    /**
     * renvoie les résultats dont le specimenCode fait partie de $selectedSpecimensCode
     * @param array $diffs
     * @param array $selectedSpecimensCode
     * @return array
     */
    public function filterBySpecimensCode($diffs,array $selectedSpecimensCode=[]) {
        $returnDiffs=$diffs;
        if (count($selectedSpecimensCode)>0) {
            // Remise du datas à zero
            $returnDiffs['datas']=[];
            $returnDiffs['classes']=$diffs['classes'];
            foreach ($diffs['classes'] as $className => $specimensCode) {
                foreach ($specimensCode as $specimenCode) {
                    if (in_array($specimenCode, $selectedSpecimensCode)) {
                        $returnDiffs['datas'][$specimenCode] = $diffs['datas'][$specimenCode] ;
                    }
                    else {
                        unset($returnDiffs['classes'][$className][$specimenCode]);
                    }
                }
            }
        }
        return $returnDiffs;
    }
    
    /**
     * filtre les résultats dont les choix ont été complétement faits
     * @param type $diffs
     * @param array $choicesToRemove
     * @return type
     */
    public function filterByChoicesDone($diffs, array $choicesToRemove=[]) {
        $returnDiffs=$diffs;
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
                    if (isset($returnDiffs['classes'][$className]) && isset($returnDiffs['classes'][$className][$specimenCode])) {
                        $totalStatFields=0;
                        foreach ($returnDiffs['classes'][$className][$specimenCode] as $diffsFields) {
                            $totalStatFields+=count($diffsFields) ;
                        }
                        if ($totalStatFields == $comptFieldChoice) {
                            unset($returnDiffs['classes'][$className][$specimenCode]) ;
                            unset($returnDiffs['datas'][$specimenCode][$className]) ;
                            if (isset($returnDiffs['datas'][$specimenCode]) && count($returnDiffs['datas'][$specimenCode]) == 0) {
                                unset($returnDiffs['datas'][$specimenCode]);
                            }
                        }
                    }
                }
            }
        }
        return $returnDiffs ;
    }
    
    /**
     * filtre les résultats
     * @param array $diffs
     * @param array $classesName
     * @param array $selectedSpecimensCode
     * @param array $choicesToRemove
     * @return array
     */
    public function filterResults($diffs, array $classesName=[], array $selectedSpecimensCode=[], array $choicesToRemove=[]) {
        $returnDiffs = $this->filterByClassesName($diffs, $classesName) ;
        $returnDiffs = $this->filterBySpecimensCode($returnDiffs, $selectedSpecimensCode);
        $returnDiffs = $this->filterByChoicesDone($returnDiffs, $choicesToRemove);
        return $returnDiffs;
    }
    
    
    public function deleteChoices()
    {
        parent::__construct($this->getPathname(), 'w+');
        parent::__construct($this->getPathname(), 'c+');
        $this->generateDiff=true;
    }
}
