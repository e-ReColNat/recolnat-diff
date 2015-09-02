<?php
namespace Recolnat\DarwinCoreBundle\Component;
use Recolnat\DarwinCoreBundle\Component\Extension\Extension;

class DarwinCoreArchive
{
    /**
     *
     * @var Extension
     */
    public $core;
    
    /**
     * @var \ArrayObject
     */
    public $extensions;


    public function __construct()
    {
        $this->extensions = new \ArrayObject();
    }
    public function getCore()
    {
        return $this->core;
    }

    public function setCore(Extension $core)
    {
        $this->core = $core;
        return $this;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function getExtension($shortType)
    {
        foreach($this->core->getLinkedExtensions() as $extension) {
            /* @var $extension Extension */
            if (strtolower($extension->getShortType())  == strtolower($shortType)) {
                return $extension;
            }
        }
        return null;
    }

    public function setExtensions(\ArrayObject $extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }

    public function setExtension(Extension $extension)
    {
        $this->extensions[] = $extension;
        return $this;
    }

}