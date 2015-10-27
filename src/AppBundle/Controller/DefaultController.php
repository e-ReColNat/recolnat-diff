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
        $diffs=$diffManager->getAllDiff('MHNAIX') ;

        $specimenRepository = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen') ;
        $diffStatsManager = $this->get('diff.stats')->init($diffs) ;
        dump($diffs);
        $stats = $diffStatsManager->getStats();
        $specimens = $specimenRepository->findBySpecimenCodes($diffStatsManager->getAllSpecimensId()) ;
        return $this->render('default/index.html.twig', array(
            'stats'                     => $stats,
            'diffs'                         => $diffs,
            'specimens'         => $specimens
        ));
    }
    
    /**
     * @Route("/test/", name="test")
     */
    public function testAction()
    {
        /* @var $diffManager \AppBundle\Manager\DiffManager */
        /*$diffManager = $this->get('diff.manager');
        $diffs=$diffManager->getAllDiff('MNHN') ;
        $diffStatsManager = $this->get('diff.stats')->init($diffs) ;*/
        $diffs = [
            "Specimen" => [
              0 => "MHNAIXAIXAIX018780"
            ],
            "Bibliography" => [],
            "Determination" => [
              0 => "MHNAIXAIXAIX028625",
              1 => "MHNAIXAIXAIX028625"
            ],
            "Localisation" => [
              0 => "MHNAIXAIXAIX003094"
            ],
            "Recolte" => [
              0 => "MHNAIXAIXAIX000429",
              1 => "MHNAIXAIXAIX000807"
            ],
            "Stratigraphy" => [
              0 => "MHNAIXAIXAIX018780"
            ],
            "Taxon" => [
              0 => "MHNAIXAIXAIX000427",
              1 => "MHNAIXAIXAIX000428",
              2 => "MHNAIXAIXAIX000429",
              3 => "MHNAIXAIXAIX000430",
              4 => "MHNAIXAIXAIX000429"
            ]
          ];
        $specimenRepository = $this->getDoctrine()->getRepository('AppBundle\Entity\Specimen') ;
        $diffStatsManager = $this->get('diff.stats')->init($diffs) ;
        dump($diffs);
        $specimens=[];
        $stats=[];
        //$stats = $diffStatsManager->getStats();
        //$specimens = $specimenRepository->findBySpecimenCodes($diffStatsManager->getAllSpecimensId()) ;
        return $this->render('default/index.html.twig', array(
            'stats'                     => $stats,
            'diffs'                         => $diffs,
            'specimens'         => $specimens
        ));
        /*$determinationRepository = $this->getDoctrine()->getRepository('AppBundle\Entity\Determination') ;
        $determination = $determinationRepository->find('737AF1D26C4E4365A6F790DFC54366C8') ;
        
        $metaData = $this->getDoctrine()->getManager()->getMetadataFactory()->getMetadataFor('AppBundle\Entity\Specimen') ;
        dump($metaData->getFieldNames()) ;
        dump($determination->getSpecimen()) ;
        dump($determination->getSpecimen()->getBibliographies()) ;
        dump($determination->getSpecimen()->getStratigraphy()) ;*/
        /*return $this->render('default/test.html.twig', array(
        ));*/
    }
    
    /**
     * @Route("/translate/", name="translate", defaults={"_format": "xml"},)
     */
    public function translateAction()
    {
        $entitiesName=[
            'Specimen',     
            'Bibliography',
            'Determination',
            'Localisation',
            'Recolte',
            'Stratigraphy',
            'Taxon'
        ];
        $translateFields = [];
        foreach($entitiesName as $name) {
            $metadata = $this->getDoctrine()->getManager()->getMetadataFactory()
                ->getMetadataFor('AppBundle:'.$name) ;
            $identifier = key(array_flip($metadata->getIdentifier())) ;
            $fields = array_flip($metadata->getFieldNames() );
            unset($fields[$identifier]) ;
            $translateFields[$name] = array_flip($fields) ;
        }
        /*$response = new Response();
        $response->headers->set('Content-Type', 'xml');*/

        return $this->render('default/translate.xml.twig', array(
            'translateFields'         => $translateFields,
        ));
    }
}
