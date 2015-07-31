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
        $extractor = $this->get('dwc.extractor')->init($path);
        $dataCore = $extractor->getCore()->getData();
        return array(
            'dataCore'      => $dataCore,
            'index'         => $extractor->getCore()->getIndexes(),
            'path'          => $path,
        );
    }
    /**
     * @Route("/compare", name="dwc-compare")
     * @Template()
     */
    public function compareAction() {
        $extractor = $this->get('dwc.extractor')->init(__DIR__.'/../Resources/files/example.zip');
        $core = $extractor->getCore()->getData();
        $extractor = $this->get('dwc.extractor')->init(__DIR__.'/../Resources/files/example2.zip');
        $core2 = $extractor->getCore()->getData();

        return array(
            'extractor'     => $extractor,
            'core'          => $core,
            'core2'          => $core2,
        );
    }
    /**
     * @Route("/record/{path}/{id}", name="dwc-record", requirements={"path"=".+"})
     * @Template()
     */
    public function recordAction($path, $id)
    {
        $extractor = $this->get('dwc.extractor')->init(urldecode($path));
        
        return array(
            'extractor'     => $extractor,
            'data'          => $extractor->getCore()->getRecord($id)
        );
    }
}
