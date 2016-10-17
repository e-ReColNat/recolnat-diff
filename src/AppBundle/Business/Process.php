<?php

namespace AppBundle\Business;


class Process extends \Symfony\Component\Process\Process
{
    public $startOutput = null;
    public $endOutput = null;

    /** @var  string */
    protected $name;
    /** @var  string */
    protected $key=null;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }



    public function getStartOutput($format = 'json')
    {
        $progress = 1;
        return $this->getFormattedOutput($format, $progress);
    }

    public function getEndOutput($format = 'json')
    {
        $progress = 100;
        return $this->getFormattedOutput($format, $progress);
    }

    public function getTimer($format = 'json')
    {
        if ($format == 'json') {
            return \json_encode(['name' => $this->getName(), 'time' => microtime(true)]);
        }
        return ['name' => $this->getName(), 'time' => microtime(true)];
    }

    /**
     * @param $format
     * @param $output
     * @return string
     */
    private function getFormattedOutput($format, $progress)
    {
        $output = ['name' => $this->getName(), 'progress' => $progress] ;
        if (!is_null($this->getKey())) {
            $output['key'] = $this->getKey() ;
        }
        if ($format == 'json') {
            return \json_encode($output);
        }

        return $output;
    }

}
