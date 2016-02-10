<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Business\User\Prefs;
use AppBundle\Form\Type\UserPrefsType;

/**
 * Description of UserController
 *
 * @author tpateffoz
 */
class UserController extends Controller
{

    /**
     * @Route("/user/{institutionCode}/prefs/view", name="viewPrefsUser")
     */
    public function indexAction($institutionCode)
    {
        /* @var $user \AppBundle\Business\User\User */
        $user = $this->get('userManager');
        $user->init($institutionCode);
        $prefs = $user->getPrefs();
        return $this->render('@App/User/viewPrefs.html.twig', array(
                    'institutionCode' => $institutionCode,
                    'prefs' => $prefs,
        ));
    }

    /**
     * @Route("/user/{institutionCode}/prefs/edit", name="editPrefsUser")
     */
    public function editAction(Request $request, $institutionCode)
    {
        /* @var $user \AppBundle\Business\User\User */
        $user = $this->get('userManager');
        $user->init($institutionCode);

        $prefs = $user->getPrefs();
        $form = $this->createForm(UserPrefsType::class, $prefs);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $translator \Symfony\Bundle\FrameworkBundle\Translation\Translator */
            $translator = $this->get('translator');
            $message = $translator->trans('prefs.save', [], 'prefs') ;
            $user->savePrefs($prefs) ;
            $this->addFlash('success',$message) ;
            return $this->redirectToRoute('viewPrefsUser', ['institutionCode'=>$institutionCode]) ;
        }
    
        return $this->render('@App/User/editPrefs.html.twig', array(
                    'institutionCode' => $institutionCode,
                    'form' => $form->createView(),
        ));
    }

}
