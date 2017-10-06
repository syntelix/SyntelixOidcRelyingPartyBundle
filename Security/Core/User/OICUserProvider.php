<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\User;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OICUserProvider.
 */
class OICUserProvider implements UserProviderInterface, UserFactoryInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $sessionKeyName = 'syntelix.oic.user.stored';

    /**
     * OICUserProvider constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        if ($this->session->has($this->sessionKeyName.$username)) {
            $user = $this->session->get($this->sessionKeyName.$username);

            if ($user->getUsername() === $username) {
                return $user;
            }
        }

        throw new UsernameNotFoundException(sprintf('Unable to find an active User object identified by "%s".', $username));
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        $user = $this->loadUserByUsername($user->getUsername());

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return 'Syntelix\\Bundle\\OidcRelyingPartyBundle\\Security\\Core\\User\\OICUser' === $class;
    }

    /**
     * {@inheritdoc}
     */
    public function createUser($username, array $roles, array $attributes)
    {
        $user = new OICUser($username, $roles, $attributes);

        $this->session->set($this->sessionKeyName.$username, $user);

        return $user;
    }
}
