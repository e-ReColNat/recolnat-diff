<?php

namespace AppBundle\Business;


use AppBundle\Manager\UtilityService;
use Symfony\Component\Filesystem\Filesystem;

class SelectedSpecimensHandler extends \SplFileObject
{
    const SELECTED_FILENAME = '/selected.json';

    /**
     * @param string $dirPath
     * @param string $userGroup
     */
    public function __construct($dirPath, $userGroup)
    {
        $path = UtilityService::createFile($dirPath.self::SELECTED_FILENAME, $userGroup);
        parent::__construct($path, 'c+');
    }

    /**
     * @return array
     */
    public function getData()
    {
        $fs = new Filesystem();
        if ($fs->exists($this->getPathname())) {
            $fileContent = json_decode(file_get_contents($this->getPathname()), true);
            if (is_null($fileContent)) {
                $fileContent = [];
            }

            return $fileContent;
        }

        return [];
    }

    /**
     * @param string $catalogNumber
     * @return array
     */
    public function add($catalogNumber)
    {
        $data = $this->getData();
        if (!in_array($catalogNumber, $data)) {
            $data[] = $catalogNumber;
            $this->save($data);
        }

        return $data;
    }

    /**
     * @param string $catalogNumber
     * @return array
     */
    public function remove($catalogNumber)
    {
        $data = $this->getData();
        $key = array_search($catalogNumber, $data);
        if ($key !== false) {
            unset($data[$key]);
            $this->save($data);
        }

        return $data;
    }

    /**
     * @param array $selectedSpecimens
     */
    public function save(array $selectedSpecimens)
    {
        $fs = new Filesystem();
        if ($fs->exists($this->getPathname())) {
            $responseJson = json_encode($selectedSpecimens, JSON_PRETTY_PRINT);
            $fs->dumpFile($this->getPathname(), $responseJson);
        }
    }
}
