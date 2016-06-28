<?php

namespace AppBundle\Security;

use AppBundle\Business\User\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class RecolnatUserProvider implements UserProviderInterface
{
    /**
     * @var string
     */
    protected $exportPath;
    /**
     * @var string
     */
    protected $apiRecolnatBaseUri;
    /**
     * @var string
     */
    protected $apiRecolnatUserPath;
    /**
     * @var string
     */
    protected $userGroup;

    /**
     * RecolnatUserProvider constructor.
     * @param String $exportPath
     * @param String $apiRecolnatBaseUri
     * @param String $apiRecolnatUserPath
     * @param String $userGroup
     */
    public function __construct($exportPath, $apiRecolnatBaseUri, $apiRecolnatUserPath, $userGroup)
    {
        $this->exportPath = $exportPath;
        $this->apiRecolnatBaseUri = $apiRecolnatBaseUri;
        $this->apiRecolnatUserPath = $apiRecolnatUserPath;
        $this->userGroup = $userGroup;
    }

    /**
     * @param string $username
     * @return User
     */
    public function loadUserByUsername($username)
    {
        if ($username) {
            $user = new User($username, $this->apiRecolnatBaseUri, $this->apiRecolnatUserPath, $this->userGroup);
            $user->init($this->exportPath);

            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    /**
     * @param UserInterface $user
     * @return User
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'AppBundle\Business\User\User';
    }
}
