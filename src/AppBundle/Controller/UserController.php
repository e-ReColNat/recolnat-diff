<?php

namespace AppBundle\Controller;

use AppBundle\Business\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Type\UserPrefsType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Description of UserController
 *
 * @author tpateffoz
 */
class UserController extends Controller
{

    /**
     * @Route("/user/prefs/view", name="viewPrefsUser")
     * @param UserInterface|User $user
     * @return Response
     */
    public function indexAction(UserInterface $user)
    {
        $prefs = $user->getPrefs();
        return $this->render('@App/User/viewPrefs.html.twig', array(
            'prefs' => $prefs,
        ));
    }

    /**
     * @Route("/user/prefs/edit", name="editPrefsUser")
     * @param UserInterface|User $user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(UserInterface $user, Request $request)
    {
        $prefs = $user->getPrefs();
        $form = $this->createForm(UserPrefsType::class, $prefs);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $translator \Symfony\Bundle\FrameworkBundle\Translation\Translator */
            $translator = $this->get('translator');
            $message = $translator->trans('prefs.saved', [], 'prefs');
            $user->savePrefs($prefs);
            $this->addFlash('success', $message);
            return $this->redirectToRoute('viewPrefsUser');
        }

        return $this->render('@App/User/editPrefs.html.twig', array(
            'form' => $form->createView(),
        ));
    }


    /**
     * @Route("/userlogout/", name="userlogout")
     */
    public function logoutAction()
    {
        $this->get('security.token_storage')->setToken(null);
        $this->get('session')->invalidate();

        $url = $this->getParameter('server_logout_url');

        return $this->redirect($url.'?service='.urlencode($this->generateUrl('index', [], UrlGeneratorInterface::ABSOLUTE_URL)));
    }
}
