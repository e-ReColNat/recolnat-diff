<?php

namespace AppBundle\Business;


use AppBundle\Manager\UtilityService;

class SelectedSpecimensHandler extends AbstractFile
{
    const FILENAME = '/selected.json';

    /**
     * @param string $dirPath
     * @param string $userGroup
     */
    public function __construct($dirPath, $userGroup)
    {
        $path = UtilityService::createFile($dirPath.self::FILENAME, $userGroup);
        parent::__construct($path, 'c+');
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

}
