<?php
namespace Recolnat\DarwinCoreBundle\Component\Extension;

use Recolnat\DarwinCoreBundle\Component\Extension\DarwinCoreClassInterface;

class Multimedia extends DarwinCoreClassAbstract implements DarwinCoreClassInterface
{

    public function __toString()
    {
        $string = $this->getData('identifier');
        if (is_null($string)) {
            return '';
        }
        return $string;
    }
    /* (non-PHPdoc)
     * @see \Recolnat\DarwinCoreBundle\Component\Extension\DarwinCoreClassInterface::getDwcName()
     */
    public function getDwcName() {
        return 'Multimedia';
    }
}