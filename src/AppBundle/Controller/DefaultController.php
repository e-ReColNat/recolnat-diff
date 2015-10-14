<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        /* @var $diffManager \AppBundle\Manager\DiffManager */
        $diffManager = $this->get('diff.manager');
        $diffs=$diffManager->getAllDiff('MNHN') ;

        /*$diffSpecimensId=$diffSpecimensId['specimens'];
        $diffBibliographiesId=$diffs['bibliographies'];
        $diffDeterminationsId=$diffs['determinations'];
        $diffLocalisationsId=$diffs['localisations'];
        $diffRecoltesId=$diffs['recoltes'];
        $diffStratigraphiesId=$diffs['stratigraphies'];
        $diffTaxonsId=$diffs['taxons'];*/

        /*$specimens =$this->getDoctrine()
                ->getRepository('AppBundle:Specimen')
                ->findById($diffSpecimensId) ;*/
        dump($diffs);

//        /* @var $diffSpecimens \AppBundle\Manager\DiffSpecimens */
//        $diffSpecimens = $this->get('diff.specimens')->init($diffSpecimensId);
//        /* @var $diffBibliographies \AppBundle\Manager\DiffBibliographies */
//        $diffBibliographies= $this->get('diff.bibliographies')->init($diffBibliographiesId);
//        /* @var $diffDeterminations \AppBundle\Manager\DiffDeterminations */
//        $diffDeterminations= $this->get('diff.determinations')->init($diffDeterminationsId);
//        /* @var $diffLocalisations \AppBundle\Manager\DiffLocalisations */
//        $diffLocalisations= $this->get('diff.localisations')->init($diffLocalisationsId);
//        /* @var $diffRecoltes \AppBundle\Manager\DiffRecoltes */
//        $diffRecoltes= $this->get('diff.recoltes')->init($diffRecoltesId);
//        /* @var $diffStratigraphies \AppBundle\Manager\DiffStratigraphies */
//        $diffStratigraphies= $this->get('diff.stratigraphies')->init($diffStratigraphiesId);
//        /* @var $diffTaxons \AppBundle\Manager\DiffTaxons */
//        $diffTaxons= $this->get('diff.taxons')->init($diffTaxonsId);

        /* @var $diffStatsManager AppBundle\Manager\DiffStatsManager */
        $diffStatsManager = $this->get('diff.stats')->init($diffs) ;
        dump($diffStatsManager->getStats()) ;
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
            'diffs' => $diffStatsManager->getStats(),
            //'specimens' => [],
        ));
    }
}
