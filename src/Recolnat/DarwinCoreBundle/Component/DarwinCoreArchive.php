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

    /**
     * 
     * @return \ArrayObject
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    public function getExtension($shortType)
    {
        foreach($this->extensions as $extension) {
            /* @var $extension Extension */
            if (strtolower($extension->getShortType())  == strtolower($shortType)) {
                return $extension;
            }
        }
        return null;
    }
    
    public function getExtensionsRows($coreId) {
        $rows = array();
        if ($this->getExtensions()->count()>0) {
            foreach ($this->getExtensions() as $extension) {
                $cloneExtension = $this->getExtensionRows($extension->getShortType(), $coreId);
                $rows[$extension->getShortType()] = $cloneExtension;
            }
        }
        return $rows;
    }
    
    public function getExtensionRows($shortType, $coreId) {
        $rows = array();
        if ($this->getExtension($shortType)) {
            $extension = $this->getExtension($shortType);
            $cloneExtension = clone $this->getExtension($shortType);
            $cloneExtension->setData($extension->getExtensionRows($coreId)) ;
            return $cloneExtension ;
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

    public function __clone() 
    {
        $this->core = clone $this->core;
        $this->extensions = clone $this->extensions;
    }
    
    public function toXml($withExtensions = FALSE)
    {
        $dwc = new \DOMDocument('1.0', 'UTF-8');
        $root = $dwc->createElementNS(
            'http://rs.tdwg.org/dwc/dwcrecord/',
            'dwr:DarwinRecordSet'
            );
        $dwc->appendChild($root) ;
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation', 'http://rs.tdwg.org/dwc/dwcrecord/ http://purl.org/dc/terms/ http://rs.tdwg.org/dwc/xsd/tdwg_dwc_classes.xsd');
        
        $node = $dwc->importNode($this->getCore()->toXml(), true);
        $root->appendChild($node) ;
        if ($withExtensions && $this->getExtensions()->count()>0) {
            foreach ($this->getExtensions() as $linkedExtension) {
                /* @var $linkedExtension Extension  */
                $node = $dwc->importNode($linkedExtension->toXml(), true);
                $root->appendChild($dwc->importNode($linkedExtension->toXml(), true));
                }
            }
//        foreach ($this->getData() as $class) {
//            $node = $dwc->importNode($class->toXml(), true);
//            if ($withExtensions && $this->getLinkedExtensions()->count()>0) {
//                foreach ($this->getLinkedExtensions() as $linkedExtension) {
//                    // @var Extension $linkedExtension 
//                    foreach ($linkedExtension->getData() as $linkedData) {
//                        if ($linkedData->getData('coreId') == $class->getData('id')) {
//                            $node->appendChild($dwc->importNode($linkedData->toXml(), true));
//                        }
//                    }
//                }
//            }
            $root->appendChild($node) ;
//        }
        return $dwc->saveXML($root);
    }
    public function getRecord($coreId) {
        $coreRecord = $this->getCore()->getData($coreId) ;
        if (!is_null($coreRecord)) {
            $coreRecord['extensions'] = array();
            foreach ($this->getExtensions() as $extension) {
                /* @var $extension Extension  */
                $coreRecord['extensions'][$extension->getShortType()]='';
            }
        }
    }
}