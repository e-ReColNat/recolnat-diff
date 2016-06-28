<?php

namespace AppBundle\Business;


class Process extends \Symfony\Component\Process\Process
{
    public $startOutput = null;
    public $endOutput = null;

    protected $name;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getStartOutput($format = 'json')
    {
        if ($format == 'json') {
            return \json_encode(['name' => $this->getName(), 'progress' => 1]);
        }

        return ['name' => $this->getName(), 'progress' => 0];
    }

    public function getEndOutput($format = 'json')
    {
        if ($format == 'json') {
            return \json_encode(['name' => $this->getName(), 'progress' => 100]);
        }

        return ['name' => $this->getName(), 'progress' => 0];
    }

    public function getTimer($format = 'json')
    {
        if ($format == 'json') {
            return \json_encode(['name' => $this->getName(), 'time' => microtime(true)]);
        }
        return ['name' => $this->getName(), 'time' => microtime(true)];
    }

}
