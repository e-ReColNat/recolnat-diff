<?php
/**
 * Created by PhpStorm.
 * User: tpateffoz
 * Date: 10/05/16
 * Time: 15:24
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class TestController extends Controller
{

    /**
     * @Route("/generateDiff/{collectionCode}/{compt}", name="generateDiff")
     * @param string  $collectionCode
     * @param integer $number
     * @return Response
     */
    public function generateDiffAction($collectionCode, $number)
    {
        $collection = $this->getDoctrine()->getManager()
            ->getRepository('AppBundle:Collection')->findOneBy(['collectioncode' => $collectionCode]);
        /* @var $diffManager \AppBundle\Manager\DiffManager */
        $diffManager = $this->get('diff.manager');
        $diffManager->generateDiff($collection, $number, rand(1, 5));

        return $this->render('@App/Front/generateDiff.html.twig');
    }
}
