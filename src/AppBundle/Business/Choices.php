<?php

namespace AppBundle\Business;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Description of Choices
 *
 * @author tpateffoz
 */
class Choices extends \SplFileObject
{

    /**
     * @param string $dirPath
     */
    public function __construct($dirPath)
    {
        parent::__construct($dirPath.'/choices.json', 'c+');
    }

    public function getContent()
    {
        $content = json_decode(file_get_contents($this->getPathname()), true);
        if (is_null($content) || !is_array($content)) {
            $content = [];
        }
        return $content;
    }

    public function save($choices)
    {
        $fs = new Filesystem();
        $fs->dumpFile($this->getPathname(), json_encode($choices, JSON_PRETTY_PRINT));
        //chmod($this->getPathname(), 0755);
    }

    public function deleteChoices()
    {
        parent::__construct($this->getPathname(), 'w+');
        parent::__construct($this->getPathname(), 'c+');
    }

}
