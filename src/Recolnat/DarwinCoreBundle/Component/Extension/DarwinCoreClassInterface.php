<?php
namespace Recolnat\DarwinCoreBundle\Component\Extension;

interface DarwinCoreClassInterface
{
    public function __toString();
    /**
     * Return the name of the extension for XML use (eg : dwc:Occurence)
     * @return String
     */
    //public function getDwcName();
    //public function createXmlNode(\DOMDocument $dwc, $key, $value);

}