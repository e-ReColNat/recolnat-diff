<?php

namespace Recolnat\DarwinCoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Recolnat\DarwinCoreBundle\Component\Extractor;
use Symfony\Component\Finder\Finder;

class DarwinCoreController extends Controller
{
    /**
     * @Route("/", name="dwc-index")
     * @Template()
     */
    public function indexAction() 
    {
        $finder = new Finder();
        $files = $finder->files()
            ->name('*.zip')
            ->in(__DIR__.'/../Resources/files')
            ->sortByName();
        
        return array(
            'files'     => $files,
        );
    }
    /**
     * @Route("/load/{path}", name="dwc-load", requirements={"path"=".+"})
     * @Template()
     */
    public function loadAction($path)
    {
        //$extractor = $this->get('dwc.extractor')->init(__DIR__.'/../Resources/files', 'canadensys_3.zip');
        $extractor = $this->get('dwc.extractor')->init($path);
        //$extractor=new Extractor(__DIR__.'/../Resources/files', 'canadensys.zip');
        //var_dump($extractor->getCore()->getData());
        $core = $extractor->getCore()->getData();
        return array(
            'core'          => $core
        );
    }
    /**
     * @Route("/compare", name="dwc-compare")
     * @Template()
     */
    public function compareAction() {
        $extractor = $this->get('dwc.extractor')->init(__DIR__.'/../Resources/files', 'example.zip');
        $core = $extractor->getCore()->getData();
        $extractor = $this->get('dwc.extractor')->init(__DIR__.'/../Resources/files', 'example2.zip');
        $core2 = $extractor->getCore()->getData();

        return array(
            'extractor'     => $extractor,
            'core'          => $core,
            'core2'          => $core2,
        );
    }
    /**
     * @Route("/record/{id}", name="dwc-record")
     * @Template()
     */
    public function recordAction($id)
    {
        $extractor = $this->get('dwc.extractor')->init(__DIR__.'/../Resources/files', 'example.zip');
        //$extractor=new Extractor(__DIR__.'/../Resources/files', 'canadensys.zip');
        //var_dump($extractor->getCore()->getData());
        
        return array(
            'extractor'     => $extractor,
            'data'          => $data
        );
    }
}
