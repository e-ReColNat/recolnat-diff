<?php

namespace AppBundle\Business;

use AppBundle\Manager\UtilityService;

class Stats extends AbstractFile
{
    public $searchDiffs;
    const FILENAME = '/stats.json';

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
}
