<?php

namespace Recolnat\DarwinCoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Recolnat\DarwinCoreBundle\Component\Extractor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Recolnat\DarwinCoreBundle\Component\DwcDiff;

class DarwinCoreController extends Controller {

    /**
     * @Route("/", name="dwc-index")
     * @Template()
     */
    public function indexAction() {
        $finder = new Finder();
        $files = $finder->files()
                ->name('*.zip')
                ->in(__DIR__ . '/../Resources/files')
                ->sortByName();

        return array(
            'files' => $files,
        );
    }

    /**
     * @Route("/load/{path}", name="dwc-load", requirements={"path"=".+"})
     * @Template()
     */
    public function loadAction($path) {
        $path = urldecode($path);
        $extractor = $this->get('dwc.extractor')->init(new File(urldecode($path)));
        $dataCore = $extractor->getCore()->getData();

        return array(
            'dataCore' => $dataCore,
            'index' => $extractor->getCore()->getIndexes(),
            'path' => $path,
            'file' => $extractor->getFullPath()
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
                ->in(__DIR__ . '/../Resources/files')
                ->sortByName();
        $formatedFileName = array();
        if (!empty($files)) {
            foreach ($files as $file) {
                $formatedFileName[$file->getRealPath()] = $file->getFileName();
            }
        }
        $form = $this->createFormBuilder()
                ->add('path1', 'choice', array(
                    'choices' => $formatedFileName,
                    'label' => 'fichier 1',
                    'expanded' => true
                ))
                ->add('path2', 'choice', array(
                    'choices' => $formatedFileName,
                    'label' => 'fichier 2',
                    'expanded' => true
                ))
                ->add('send', 'submit', array(
                    'label' => 'Comparer'
                ))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('dwc-diff', $data, 307);
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/diff", name="dwc-diff")
     * @Template()
     */
    public function diffAction(Request $request) {
        $path1 = $request->get('path1');
        $path2 = $request->get('path2');
        /* @var $dwcDiff DwcDiff  */
        $dwcDiff = $this->get('dwc.diff')->init(new File($path1), new File($path2));

        $diffHtml = $dwcDiff->getDiff('html');
        $identification = $dwcDiff->extractor1->darwinCoreArchive->getExtension('identification');
        return array(
            'dwcDiff'              => $dwcDiff,
            'diffHtml'              => $diffHtml,
            'identification'     => $identification
        );
    }

    /**
     * @Route("/record/{path}/{id}", name="dwc-record", requirements={"path"=".+"})
     * @Template()
     */
    public function recordAction($path, $id) {
        $extractor = $this->get('dwc.extractor')->init(urldecode($path));

        return array(
            'extractor' => $extractor,
            'data' => $extractor->getCore()->getRecord($id)
        );
    }

}
