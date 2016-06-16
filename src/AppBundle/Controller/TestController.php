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
     * @Route("/testdate")
     */
    public function testDateAction()
    {
        $minDate = $this->getDoctrine()->getRepository('AppBundle:Specimen')->getMinDate('AIX');
        dump($minDate);
        return $this->render('@App/base.html.twig');
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
