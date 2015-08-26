<?php

namespace Recolnat\DarwinCoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Recolnat\DarwinCoreBundle\Component\Extractor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

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
        $path = urldecode($path) ;
        $extractor = $this->get('dwc.extractor')->init($path);
        $dataCore = $extractor->getCore()->getData();
        
        return array(
            'dataCore'      => $dataCore,
            'index'         => $extractor->getCore()->getIndexes(),
            'path'          => $path,
            'file'          => $extractor->getFullPath()
        );
    }
    /**
     * @Route("/compare", name="dwc-compare")
     * @Template()
     */
    public function compareAction(Request $request) {
        $finder = new Finder();
        $files = $finder->files()
            ->name('*.zip')
            ->in(__DIR__.'/../Resources/files')
            ->sortByName();
        $formatedFileName = array();
        if (!empty($files)) {
            foreach ($files as $file) {
                $formatedFileName[$file->getRealPath()] = $file->getFileName(); 
            }
        }
        $form = $this->createFormBuilder()
            ->add('path1', 'choice', array(
                'choices'   => $formatedFileName,
                'label'     => 'fichier 1',
                'expanded'  => true
            ))
            ->add('path2', 'choice', array(
                'choices'   => $formatedFileName,
                'label'     => 'fichier 2',
                'expanded'  => true
            ))
            ->add('send', 'submit', array(
                'label'     => 'Comparer'
            ))
            ->getForm();
        
        $form->handleRequest($request);
            
        if ($form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('dwc-diff', $data, 307);
        }
        return array ('form' => $form->createView());
    }
    /**
     * @Route("/diff", name="dwc-diff")
     * @Template()
     */
    public function diffAction(Request $request) {
        $path1 = $request->get('path1');
        $path2 = $request->get('path2');
        $extractor = $this->get('dwc.extractor')->init($path1);
        $core = $extractor->getCore()->getData();
        $extractor = $this->get('dwc.extractor')->init($path2);
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
