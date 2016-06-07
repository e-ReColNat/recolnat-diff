<?php
/**
 * Created by PhpStorm.
 * User: tpateffoz
 * Date: 10/05/16
 * Time: 15:24
 */

namespace AppBundle\Controller;

use AppBundle\Business\User\User;
use AppBundle\Manager\UtilityService;
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
     * @Route("/testdate")
     */
    public function testDateAction()
    {
        $minDate = $this->getDoctrine()->getRepository('AppBundle:Specimen')->getMinDate('AIX');
        dump($minDate);
        return $this->render('@App/base.html.twig');
    }
    /**
     * @Route("/institutions/", name="insitutions")
     */
    public function listeInstitutionAction()
    {
        $fakeUser = new User('Julien.Husson', '1234', '123456', $this->getParameter('api_recolnat_user'),
            $this->getParameter('user_group'));

        $permissions = $fakeUser->getPermissions();
        dump($permissions);
        dump($fakeUser->getManagedCollections());
        $managedCollections = $this->getDoctrine()->getManager()
            ->getRepository('AppBundle:Collection')->findBy(['collectioncode' => $fakeUser->getManagedCollections()]);

        return $this->render('@App/Test/institutions.html.twig', [
            'permissions' => $permissions,
            'managedCollections' => $managedCollections,
        ]);
    }

    /**
     * @Route("files")
     * @return Response
     */
    public function filesAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $testDir = $user->getDataDirPath().'test/';
        $testFile = $testDir.'test.txt';
        $userGroup = $this->getParameter('user_group');
        UtilityService::createDir($testDir, $userGroup);
        UtilityService::createFile($testFile, $userGroup);
        return $this->render('@App/base.html.twig', [
        ]);
    }
}
