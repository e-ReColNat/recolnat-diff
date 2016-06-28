<?php

namespace AppBundle\Business;

use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractFile extends \SplFileObject
{

    public $data = null;

    /**
     * @param array $data
     */
    public function save(array $data)
    {
        $fs = new Filesystem();
        if ($fs->exists($this->getPathname())) {
            $responseJson = json_encode($data, JSON_PRETTY_PRINT);
            $fs->dumpFile($this->getPathname(), $responseJson);
        }
    }


    /**
     * @return array|mixed
     */
    public function getData()
    {
        if (is_null($this->data)) {
            $this->data = [];
            $fs = new Filesystem();
            if ($fs->exists($this->getPathname())) {
                $fileContent = json_decode(file_get_contents($this->getPathname()), true);
                if (is_null($fileContent)) {
                    $fileContent = [];
                }

                $this->data = $fileContent;
            }
        }

        return $this->data;
    }
}
