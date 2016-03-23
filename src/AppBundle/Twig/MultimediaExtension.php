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

    /**
     * @param string  $url
     * @param integer $width
     * @return string
     */
    public function getThumb($url, $width)
    {
        $parseUrl = parse_url($url);
        $returnThumb = '';
        switch ($parseUrl['host']) {
            case 'dsiphoto.mnhn.fr':
                $returnThumb = 'http://imager.mnhn.fr/imager/v'.$width.$parseUrl['path'];
                break;
            case 'sonneratphoto.mnhn.fr':
                $returnThumb = 'http://imager.mnhn.fr/imager2/v'.$width.$parseUrl['path'];
                break;
            case 'mediaphoto.mnhn.fr':
                $returnThumb = 'http://imager.mnhn.fr/imager3/v'.$width.$parseUrl['path'];
                break;
        }

        return $returnThumb;
    }

    public function getName()
    {
        return 'multimedia_extension';
    }
}
