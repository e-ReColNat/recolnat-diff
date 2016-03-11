<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Twig;

/**
 * Description of MultimediaExtension
 *
 * @author tpateffoz
 */
class MultimediaExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('thumb', array($this, 'getThumb'))
        );
    }

    public function getThumb($url, $width) {
        $parseUrl = parse_url($url);
        switch ($parseUrl['host']) {
            case 'dsiphoto.mnhn.fr':
                return 'http://imager.mnhn.fr/imager/v'.$width.$parseUrl['path'];
            case 'sonneratphoto.mnhn.fr':
               return 'http://imager.mnhn.fr/imager2/v'.$width.$parseUrl['path'];
            case 'mediaphoto.mnhn.fr':
               return 'http://imager.mnhn.fr/imager3/v'.$width.$parseUrl['path'];
        }
    }

    public function getName()
    {
        return 'multimedia_extension';
    }
}
