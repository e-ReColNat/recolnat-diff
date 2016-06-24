<?php
/**
 * Created by PhpStorm.
 * User: tpateffoz
 * Date: 10/05/16
 * Time: 15:24
 */

namespace AppBundle\Controller;

use AppBundle\Business\DiffHandler;
use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Business\ListMatchUps;
use AppBundle\Business\SelectedSpecimensHandler;
use AppBundle\Business\SessionHandler;
use AppBundle\Business\User\User;
use AppBundle\Manager\DiffBibliography;
use AppBundle\Manager\UtilityService;
use Doctrine\ORM\AbstractQuery;
use JsonStreamingParser\Listener\IdleListener;
use JsonStreamingParser\Listener\InMemoryListener;
use JsonStreamingParser\Parser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;


class TestController extends Controller
{

    /**
     * @Route("/testdate")
     */
    public function testDateAction()
    {
        $minDate = $this->getDoctrine()->getRepository('AppBundle:Specimen')->getMinDate('AIX');
        dump($minDate);

        return $this->render('@App/base.html.twig');
    }

    /**
     * @Route("files")
     * @return Response
     */
    public function filesAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $testDir = $user->getDataDirPath().'test/';
        $testFile = $testDir.'test.txt';
        $userGroup = $this->getParameter('user_group');
        UtilityService::createDir($testDir, $userGroup);
        UtilityService::createFile($testFile, $userGroup);

        return $this->render('@App/base.html.twig', [
        ]);
    }

    /**
     * @Route("json2")
     */
    public function json2Action()
    {
        $jsonFile = '/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/diffs.json' ;
        $classesJsonFile = '/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/MHNAIX/AIX/classes.json';
        $collectionCode = 'AIX';

        /*
        $content = file_get_contents($jsonFile);
        $json = \json_decode($content);

        $classes = $json->classes ;
        dump($classes);

        $fs = new Filesystem();
        $file = fopen($classesJsonFile, 'w+');
        $fs->dumpFile($classesJsonFile, \json_encode($classes, JSON_PRETTY_PRINT));
        */
        $classes = \json_decode(file_get_contents($classesJsonFile));
        //dump($classes);
        /*foreach($classes['Bibliography'] as $catalogNumbers) {

        }*/

        $collection = $this->get('utility')->getCollection($collectionCode);

        $recordsRecolnat = $this->getDoctrine()->getRepository('AppBundle:Bibliography')
            ->findByCatalogNumbers($collection, $classes->Bibliography, AbstractQuery::HYDRATE_ARRAY);
        dump($recordsRecolnat);

        $diffBiblio = new DiffBibliography($this->getDoctrine(), 500);
        dump(array_slice($classes->Bibliography, 0, 100));

        $diffBiblio->init($collection, 'Bibliography', array_slice($classes->Bibliography, 0, 100));
        dump($diffBiblio->getStats());
        //$diffBiblio->
        return $this->render('@App/base.html.twig', [
        ]);
    }

    /** @Route("testExport") */
    public function testExport()
    {
        $collection = $this->get('utility')->getCollection('AIX');
        $filesystem = new Filesystem();
        $exportPrefs = new ExportPrefs();
        $exportPrefs->setSideForChoicesNotSet('recolnat');
        $exportPrefs->setSideForNewRecords('recolnat');

        $dwcFilePath = $this->get('exportmanager')->init($this->getUser())
            ->setCollectionCode($collection->getCollectioncode())->export('dwc', $exportPrefs);
        /*$diffComputer = $this->get('diff.computer');


        $entityName = 'Localisation';
        $savePath = '/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/MHNAIX/AIX';
        $fileCatalogNumbers = new \SplFileObject($savePath.'/catalogNumbers_'.$entityName.'.json');
        $catalogNumbers[$entityName] = json_decode(file_get_contents($fileCatalogNumbers->getPathname()), true);

        $diffComputer->setCollection($collection);

        $diffComputer->setCatalogNumbers($catalogNumbers);
        $diffComputer->computeClassname($entityName);

        $datas = $diffComputer->getAllDatas();*/

        return $this->render('@App/base.html.twig', [
        ]);
    }
    /** @Route("testLonesome") */
    public function testLonesomeAction()
    {
        $collectionCode = 'AIX';
        $collection = $this->get('utility')->getCollection($collectionCode);
        $exportManager = $this->get('exportmanager')->init($this->getUser());

        $exportManager->setCollectionCode($collectionCode);

        $lonesomes = $exportManager->getDiffHandler()->getLonesomeRecordsFile()->getLonesomeRecordsByBase('recolnat');

        dump($lonesomes);
        return $this->render('@App/base.html.twig', [
        ]);
    }
    /**
     * @Route("json3")
     */
    public function json3Action()
    {
        /*$file = '/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/diffs.json';
        $allData = \json_decode(file_get_contents($file), true);
        $lonesomeRecords = $allData['lonesomeRecords'] ;
        $reformatted = [];
        dump(current($lonesomeRecords['Specimen']['recolnat']));
        foreach($lonesomeRecords as $class=>$items) {
            foreach($items as $db=>$itemsPerDb) {
                foreach ($itemsPerDb as $item) {
                    if (!isset($reformatted[$class])) {
                        $reformatted[$class] = [0 => [], 1 => []];
                    }
                    if ($db == 'recolnat') {
                        $index = 0;
                    } else {
                        $index = 1;
                    }
                    $reformatted[$class][$index][] = [
                        0 => $item['catalogNumber'],
                        1 => $item['id'],
                        2 => $item['taxon'],
                    ];
                }
            }
        }
        dump(current($reformatted['Specimen'][0]));
        $fs = new Filesystem();
        $utility = $this->get('utility');
        $utility::createFile('/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/lonesome1.json');
        $utility::createFile('/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/lonesome2.json');
        $fs->dumpFile('/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/lonesome1.json', \json_encode($lonesomeRecords, JSON_PRETTY_PRINT));
        $fs->dumpFile('/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/lonesome2.json', \json_encode($reformatted, JSON_PRETTY_PRINT));/*/

        $jsonFile1 = '/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/lonesome1.json';
        $jsonFile2 = '/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/lonesome2.json';
        $stopWatch = new Stopwatch();
        /*$stopWatch->start('file1');
        $arrayFile1 = \json_decode(file_get_contents($jsonFile1));
        $event1 = $stopWatch->stop('file1');*/
        $stopWatch->start('file2');
        $arrayFile2 = \json_decode(file_get_contents($jsonFile2), true);
        $event2 = $stopWatch->stop('file2');

        //dump($event1->getPeriods());
        dump($event2->getPeriods());





        //dump($allData);
        /*$catalogNumbers=['AIX000087',"AIX013097",
            "AIX020865",
            "AIX000397",
            "AIX019963",
            "AIX000226","AIX000218",
            "AIX000233",
            "AIX000245",
            "AIX000266",
            "AIX000318",
            "AIX000407",
            "AIX000010",
            "AIX000011",
            "AIX000050"];
        $diff=[];
        $lonesomes=[];
        $classesLonesome = ['Specimen'];
        foreach($allData['datas'] as $catalogNumber=>$data) {
            if (in_array($catalogNumber, $catalogNumbers)) {
                $diff[$catalogNumber]=$data;
            }
        }
        foreach($allData['lonesomeRecords'] as $className=>$data) {
            if (in_array($className, $classesLonesome)) {
                $lonesomes[$className]=$data;
            }
        }
        dump($diff);
        dump($lonesomes);*/
        return $this->render('@App/base.html.twig', [
        ]);
    }

    protected $stack;
    protected $keys;
    protected $result;
    protected function startComplexValue($type)
    {
        // We keep a stack of complex values (i.e. arrays and objects) as we build them,
        // tagged with the type that they are so we know how to add new values.
        $current_item = ['type' => $type, 'value' => []];
        $this->stack[] = $current_item;
    }

    protected function endComplexValue()
    {
        $obj = array_pop($this->stack);

        // If the value stack is now empty, we're done parsing the document, so we can
        // move the result into place so that getJson() can return it. Otherwise, we
        // associate the value
        if (empty($this->stack)) {
            $this->result = $obj['value'];
        } else {
            $this->insertValue($obj['value']);
        }
    }

    // Inserts the given value into the top value on the stack in the appropriate way,
    // based on whether that value is an array or an object.
    protected function insertValue($value)
    {
        // Grab the top item from the stack that we're currently parsing.
        $current_item = array_pop($this->stack);

        // Examine the current item, and then:
        //   - if it's an object, associate the newly-parsed value with the most recent key
        //   - if it's an array, push the newly-parsed value to the array
        if ($current_item['type'] === 'object') {
            $current_item['value'][array_pop($this->keys)] = $value;
        } else {
            $current_item['value'][] = $value;
        }

        // Replace the current item on the stack.
        $this->stack[] = $current_item;
    }

    protected function parseSpecimen($reader, &$diffs)
    {
        $catalogNumber = $reader->value;

        $minDepth = $reader->currentDepth;
        $endLoop = false;
        while (!$endLoop && $reader->read()) {
            switch ($reader->tokenType) {
                case \JSONReader::ARRAY_START:
                    $this->startComplexValue('array');
                    break;
                case \JSONReader::ARRAY_END:
                    $this->endComplexValue();
                    if ($reader->currentDepth <= $minDepth) {
                        $endLoop = true;
                        break 2;
                    }
                    break;
                case \JSONReader::OBJECT_START:
                    $this->startComplexValue('object');
                    break;
                case \JSONReader::OBJECT_KEY:
                    $this->keys[] = $reader->value;
                    break;
                case \JSONReader::NULL:
                    $this->insertValue(null);
                    break;
                case \JSONReader::VALUE:
                case \JSONReader::STRING:
                $this->insertValue($reader->value);
                break;
                case \JSONReader::OBJECT_END:
                    $this->endComplexValue();
                    if ($reader->currentDepth <= $minDepth) {
                        $endLoop = true;
                        break 2;
                    }
                    break;
            }
        } ;
        $diffs[$catalogNumber] = $this->result;
    }
    /**
     * @Route("json")
     */
    public function jsonAction()
    {

        //var_dump($listener->getJson());
        //$fileHandler = new \SplFileObject('/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/diffs.json');
        //var_dump(file_get_contents($fileHandler->getPathname()));
        //$fileContent = json_decode(file_get_contents('/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/diffs.json'), true);

        //dump($fileContent);

        $reader = new \JSONReader();
        $reader->open('/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/diffs.json');

        /*$arr = array();
        while ($reader->read()) {
            if ($reader->tokenType == \JSONReader::OBJECT_KEY) {
                $key = $reader->value;
                if (! $reader->read() || ! $reader->tokenType == \JSONReader::VALUE) {
                    echo "failed reading value of $key!\n";
                    exit;
                }
                $value = $reader->value;
                $arr[$key] = $value;
            }
        }
        var_dump($arr);*/

        echo '\JSONReader::OBJECT_START : '.\JSONReader::OBJECT_START.'<br />';
        echo '\JSONReader::OBJECT_END : '.\JSONReader::OBJECT_END.'<br />';
        echo '\JSONReader::OBJECT_KEY : '.\JSONReader::OBJECT_KEY.'<br />';
        echo '\JSONReader::STRING : '.\JSONReader::STRING.'<br />';
        echo '\JSONReader::NULL : '.\JSONReader::NULL.'<br />';
        echo '\JSONReader::FALSE : '.\JSONReader::FALSE.'<br />';
        echo '\JSONReader::TRUE : '.\JSONReader::TRUE.'<br />';
        echo '\JSONReader::FLOAT : '.\JSONReader::FLOAT.'<br />';
        echo '\JSONReader::ARRAY_START : '.\JSONReader::ARRAY_START.'<br />';
        echo '\JSONReader::ARRAY_END : '.\JSONReader::ARRAY_END.'<br />';
        echo '\JSONReader::BOOLEAN : '.\JSONReader::BOOLEAN.'<br />';
        echo '\JSONReader::NUMBER : '.\JSONReader::NUMBER.'<br />';
        echo '\JSONReader::VALUE : '.\JSONReader::VALUE.'<br />';

        $catalogNumbers=['AIX000087',"AIX013097",
            "AIX020865",
            "AIX000397",
            "AIX019963",
            "AIX000226","AIX000218",
            "AIX000233",
            "AIX000245",
            "AIX000266",
            "AIX000318",
            "AIX000407",
            "AIX000010",
            "AIX000011",
            "AIX000050"];
        $diffs=[];
        $lonesomes=[];
        $inDatas = false;
        $inLonesome = false;
        $classesLonesome = ['Specimen'];

        while ($reader->read()) {
            $currentDepth = $reader->currentDepth;
            $value = $reader->value;
            $tokenType = $reader->tokenType;
            switch($tokenType) {
                case \JSONReader::OBJECT_END:
                    if ($currentDepth == 1 && $inDatas) {
                        $inDatas = false;
                        //break 2;
                    }
                    if ($currentDepth == 1 && $inLonesome) {
                        $inLonesome = false;
                        break 2;
                    }
                    break;
                case \JSONReader::STRING:

                    break;
                case \JSONReader::OBJECT_KEY:
                    if ($inDatas) {
                        if ($currentDepth == 2) {
                            $catalogNumber = $value;
                            if (in_array($catalogNumber, $catalogNumbers)) {
                                $this->parseSpecimen($reader, $diffs);
                            }
                        }
                    }
                    if ($inLonesome) {
                        if ($currentDepth == 2) {
                            $className = $value;
                            if (in_array($className, $classesLonesome)) {
                                $this->parseSpecimen($reader, $lonesomes);
                            }
                        }
                    }
                    if ($value == 'datas') {
                        $inDatas = true;
                    }
                    if ($value == 'lonesomeRecords') {
                        $inLonesome = true;
                    }

                    break;
            }
        }
        dump($diffs);
        dump($lonesomes);
        /*while ($reader->read()) {
            switch($reader->tokenType) {
                case \JSONReader::OBJECT_START:
                    echo "object start:\n";
                    break;

                case \JSONReader::OBJECT_KEY:
                    echo "key : ".$reader->value."\n";
                    break;

                case \JSONReader::OBJECT_END:
                    echo "object start:\n";
                    break;

                case \JSONReader::ARRAY_END:
                    echo "Array end.\n";
                    break;

                case \JSONReader::VALUE:
                    echo " - " . $reader->value . "\n";
                    break;
                case \JSONReader::STRING;
                    echo " - " . $reader->value . "\n";
                    break;
            }
        }*/
        /*while ($reader->read()) {
            if ($reader->tokenType == \JSONReader::STRING) {
                // print indent
                echo str_repeat("  ", $reader->currentDepth);
                echo "* $reader->value\n";
            } elseif ($reader->tokenType == \JSONReader::ARRAY_START) {
                echo str_repeat("-", $reader->currentDepth) . ">\n";
            }
        }*/

        //$reader->close();
        //$this->get('session')->clear();
        //$this->get('session')->set('testData', $fileContent);

        //$fileContent = $this->get('session')->get('testData');
        //$this->get('session')->clear();
        /*$fs = new Filesystem();
        $utility = $this->get('utility');
        $utility::createFile('/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/diffs2.json');
        $fs->dumpFile('/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/diffs2.json', \json_encode($fileContent));*/
/*
        $collectionCode='CLF';
        $collection = $this->get('utility')->getCollection($collectionCode);
        //$managerRegistry = $this->get('manager_')

        $sessionManager = $this->get('session');
        $genericEntityManager = $this->get('genericentitymanager');
        if (is_null($collection)) {
            throw new \Exception('Can\'t found the collection with collectionCode = '.$collectionCode);
        } else {
            $diffHandler = new DiffHandler($this->getUser()->getDataDirPath(), $collection, $this->getParameter('user_group'));

            if (!$diffHandler->shouldSearchDiffs()) {
                $selectedSpecimensHandler = new SelectedSpecimensHandler($diffHandler->getCollectionPath(),
                    $this->getParameter('user_group'));
                $data = $diffHandler->getDiffsFile()->getData();
                $data['selectedSpecimens'] = $selectedSpecimensHandler->getData();
                $sessionHandler = new SessionHandler($sessionManager, $genericEntityManager, $data);
                $sessionHandler->init($diffHandler, $collectionCode);
            }
        }

        //$lonesomeRecords = $diffHandler->getDiffsFile()->getLonesomeRecordsOrderedByCatalogNumbers('recolnat', 'Determination');
        $lonesomeRecords = $diffHandler->getDiffsFile()->getLonesomeRecords('recolnat', 'Determination');*/

        /* @var $exportManager \AppBundle\Manager\ExportManager */
        //$exportManager = $this->get('exportmanager')->init($this->getUser())->setCollectionCode($collectionCode);

        /*$fileHandler = new \SplFileObject('/home/tpateffoz/www/recolnat-diff/src/AppBundle/Data/tpateffoz/CLF/CLF/diffs.json');
        //var_dump(file_get_contents($fileHandler->getPathname()));
        $fileContent = json_decode(file_get_contents($fileHandler->getPathname()), true);

        $datas = $fileContent['lonesomeRecords'];*/

        return $this->render('@App/base.html.twig', [
        ]);
        /*
        return $this->render('@App/Test/json.html.twig', [
            'datas' => $datas,
        ]);*/
    }
}
