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

        $specimenRepository = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen') ;
        $diffStatsManager = $this->get('diff.stats')->init($diffs) ;
        
        $specimens=[];
        /* @var $specimenManager \AppBundle\Manager\SpecimenManager */
        /*$specimenManager = $this->get('specimenManager')->init('recolnat') ;
        $specimens = $specimenManager->getSpecimensWithBestDetermination(
                null, $diffStatsManager->getAllSpecimensId()
                ) ;
        var_dump($specimens) ;*/
        $specimens = $specimenRepository->findBySpecimenCodes($diffStatsManager->getAllSpecimensId()) ;
        return $this->render('default/index.html.twig', array(
            'diffs'                     => $diffStatsManager->getStats(),
            'specimens'         => $specimens
        ));
    }
    
    /**
     * @Route("/test/", name="test")
     */
    public function testAction()
    {
        /* @var $diffManager \AppBundle\Manager\DiffManager */
        $diffManager = $this->get('diff.manager');
        $diffs=$diffManager->getAllDiff('MNHN') ;
        $diffStatsManager = $this->get('diff.stats')->init($diffs) ;
        /*$determinationRepository = $this->getDoctrine()->getRepository('AppBundle\Entity\Determination') ;
        $determination = $determinationRepository->find('737AF1D26C4E4365A6F790DFC54366C8') ;
        
        $metaData = $this->getDoctrine()->getManager()->getMetadataFactory()->getMetadataFor('AppBundle\Entity\Specimen') ;
        dump($metaData->getFieldNames()) ;
        dump($determination->getSpecimen()) ;
        dump($determination->getSpecimen()->getBibliographies()) ;
        dump($determination->getSpecimen()->getStratigraphy()) ;*/
        return $this->render('default/test.html.twig', array(
        ));
    }
}
