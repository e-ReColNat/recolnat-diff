<?php
namespace Recolnat\DarwinCoreBundle\Twig;

class FileExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'getHumanFilesize' => new \Twig_SimpleFunction('getHumanFilesize', array($this, 'getHumanFilesize'))
        );
    }
    public function getHumanFilesize($size, $precision = 2) {
        $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }
        return round($size, $precision).$units[$i];
    }
    public function getName()
    {
        return 'fileExtension';
    }
}