<?php

namespace AppBundle\Listener;


use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    protected $managerRegistry;
    protected $tokenStorage;

    /**
     * LoginListener constructor.
     * @param ManagerRegistry $managerRegistry
     * @param TokenStorage    $tokenStorage
     */
    public function __construct(ManagerRegistry $managerRegistry, TokenStorage $tokenStorage)
    {
        $this->managerRegistry = $managerRegistry;
        $this->tokenStorage = $tokenStorage;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $institution = $this->managerRegistry->getRepository('AppBundle:Institution')->findOneBy(['institutioncode'=>'MHNAIX']);

        dump($institution);
        $this->tokenStorage->getToken()->getUser()->setInstitution($institution);
    }
}
