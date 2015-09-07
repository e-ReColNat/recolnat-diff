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
        /* @var $dwcArchive \Recolnat\DarwinCoreBundle\Component\DarwinCoreArchive */
        $dwcArchive = $extractor->getDarwinCoreArchive() ;

        return array(
            'core' => $dwcArchive->getCore(),
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
        $dwc1 = $this->get('dwc.extractor')->init(new File($path1))->getDarwinCoreArchive() ;
        $dwc2 = $this->get('dwc.extractor')->init(new File($path2))->getDarwinCoreArchive() ;
        $dwcDiff = new DwcDiff($dwc1, $dwc2);

        $diffHtml = $dwcDiff->getDiff('html');
        $identification = $dwc1->getExtension('identification');
        return array(
            'dwc1'                  => $dwc1,
            'dwc2'                  => $dwc2,
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
