<?php
namespace Recolnat\DarwinCoreBundle\Component\Extension;

use Recolnat\DarwinCoreBundle\Component\Extension\DarwinCoreClassInterface;

class Occurrence extends Extension implements DarwinCoreClassInterface
{

    public function __toString()
    {

    }

     /* (non-PHPdoc)
      * @see \Recolnat\DarwinCoreBundle\Component\Extension\DarwinCoreClassInterface::getDwcName()
      */
     public function getDwcName() {
        return 'Occurence';
     }

    /**
     * 
     * @param \DOMDocument $dwc
     * @param unknown $key
     * @param unknown $value
     * @return \DOMElement
     */
    /*public function createXmlNode(\DOMDocument $dwc, $key, $value) {
        switch ($key) {
            case 'id':
            case 'type' :
            case 'language' :
            case 'licence' :
            case 'rightsHolder':
            case 'accessRights' :
            case 'bibliographicCitation' :
            case 'references' :
                $node = $dwc->createElementNS('http://purl.org/dc/terms/', 'dc:'.$key, $value);
                break;
            default:
                $node = $dwc->createElementNS('http://rs.tdwg.org/dwc/terms/', 'dwc:'.$key, $value);
        }
        return $node;
    }*/
}