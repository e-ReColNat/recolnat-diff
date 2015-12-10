<?php
namespace AppBundle\Business;

/**
 * Description of Choices
 *
 * @author tpateffoz
 */
class Choices extends \SplFileObject
{
   
    public function __construct($dirPath)
    {
        parent::__construct($dirPath.'/choices.json', 'c+');
    }
    
    public function getContent() {
        $content = json_decode(file_get_contents($this->getPathname()), true);
        if (is_null($content) || !is_array($content)) {
            $content=[];
        }
    }
    public function save($choices) {
        $fs = new \Symfony\Component\Filesystem\Filesystem() ;
        $fs->dumpFile($this->getPathname(), json_encode($choices, JSON_PRETTY_PRINT)) ;
    }
}
