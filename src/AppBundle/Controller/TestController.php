<?php
/**
 * Created by PhpStorm.
 * User: tpateffoz
 * Date: 10/05/16
 * Time: 15:24
 */

namespace AppBundle\Controller;

use AppBundle\Business\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

    /**
     * @Route("/institutions/", name="insitutions")
     */
    public function listeInstitutionAction()
    {
        $fakeUser = new User('Julien.Husson', '1234', '123456', [], $this->getParameter('api_recolnat_user'));

        $permissions = $fakeUser->getPermissions();
        dump($permissions);
        dump($fakeUser->getManagedCollections());
        $managedCollections = $this->getDoctrine()->getManager()
            ->getRepository('AppBundle:Collection')->findBy(['collectioncode'=>$fakeUser->getManagedCollections()]);

        return $this->render('@App/Test/institutions.html.twig', [
            'permissions' => $permissions,
            'managedCollections' => $managedCollections,
        ]);
    }
}
