<?php
namespace Recolnat\DarwinCoreBundle\Component\Extension;

use Recolnat\DarwinCoreBundle\Component\Extension\DarwinCoreClassInterface;

class Identification extends Extension implements DarwinCoreClassInterface
{

    public function __toString()
    {
        $string = $this->getData('identifiedby');
        if (is_null($string)) {
            return '';
        }
        return $string;
    }
    public function getDwcName() {
        return 'Identification';
    }
}