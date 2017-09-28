<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Authentication\Provider;

use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Authentication\Token\OICToken;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\User\UserFactoryInterface;
use Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\ResourceOwnerInterface;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\InvalidIdSignatureException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\ChainUserProvider;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * OICProvider.
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class OICProvider implements AuthenticationProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var ResourceOwnerInterface
     */
    private $resourceOwner;

    /**
     * @var OICToken
     */
    private $token;

    /**
     * @var array
     */
    private $createdUsersRoles;

    /**
     * @var array
     */
    private $hideUserNotFound = false;

    /**
     * @var bool
     */
    private $createUsers = true;

    /**
     * OICProvider constructor.
     *
     * @param UserProviderInterface  $userProvider
     * @param ResourceOwnerInterface $resourceOwner
     * @param bool                   $createUsers
     * @param array|null             $createdUsersRoles
     */
    public function __construct(UserProviderInterface $userProvider,
            ResourceOwnerInterface $resourceOwner,
            $createUsers = false, array $createdUsersRoles = null)
    {
        $this->userProvider = $userProvider;
        $this->resourceOwner = $resourceOwner;
        $this->createUsers = $createUsers;
        $this->createdUsersRoles = $createdUsersRoles;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return;
        }

        $this->token = $token;

        $user = $this->provideUser($this->token->getUsername());

        if ($user->getUsername() === $this->token->getUsername()) {
            $reloadedToken = new OICToken($user->getRoles());
            $reloadedToken->setAccessToken($this->token->getAccessToken());
            $reloadedToken->setIdToken($this->token->getIdToken());
            $reloadedToken->setRefreshToken($this->token->getRefreshToken());
            $reloadedToken->setUser($user);
            $reloadedToken->setAuthenticated(true);

            return $reloadedToken;
        }

        throw new AuthenticationException('The OpenID Connect authentication failed.');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof OICToken;
    }

    /**
     * @throws UsernameNotFoundException
     * @throws BadCredentialsException
     *
     * @param string $username
     *
     * @return UserInterface
     */
    protected function provideUser($username)
    {
        try {
            $user = $this->retrieveUser($username);
        } catch (UsernameNotFoundException $notFound) {
            if ($this->createUsers
                    && ($this->userProvider instanceof UserFactoryInterface
                            || $this->userProvider instanceof ChainUserProvider)) {
                $user = $this->createUser($username);
            } elseif ($this->hideUserNotFound) {
                throw new BadCredentialsException('Bad credentials', 0, $notFound);
            } else {
                throw $notFound;
            }
        }

        return $user;
    }

    /**
     * @throws AuthenticationServiceException
     *
     * @param string $username
     *
     * @return UserInterface
     */
    protected function retrieveUser($username)
    {
        try {
            $user = $this->userProvider->loadUserByUsername($username);

            if (!$user instanceof UserInterface) {
                throw new AuthenticationServiceException('The user provider must return an UserInterface object.');
            }
        } catch (UsernameNotFoundException $notFound) {
            throw $notFound;
        } catch (\Exception $repositoryProblem) {
            throw new AuthenticationServiceException($repositoryProblem->getMessage(), 0, $repositoryProblem);
        }

        return $user;
    }

    /**
     * @throws AuthenticationServiceException
     *
     * @param string $username
     *
     * @return UserInterface
     */
    protected function createUser($username)
    {
        $userProvider = $this->userProvider;

        if ($this->userProvider instanceof ChainUserProvider) {
            foreach ($this->userProvider->getProviders() as $userProviderTmp) {
                if ($userProviderTmp instanceof UserFactoryInterface) {
                    $userProvider = $userProviderTmp;
                }
            }
        }

        if (!$userProvider instanceof UserFactoryInterface) {
            throw new AuthenticationServiceException('UserProvider must implement UserFactoryInterface to create unknown users.');
        }

        try {
            $attributes = $this->resourceOwner->getEndUserinfo($this->token);

            $user = $userProvider->createUser($username, $this->createdUsersRoles, $attributes);

            if (!$user instanceof UserInterface) {
                throw new AuthenticationServiceException('The user provider must create an UserInterface object.');
            }
        } catch (InvalidIdSignatureException $invalidIdSignatureException) {
            throw $invalidIdSignatureException;
        } catch (\Exception $repositoryProblem) {
            throw new AuthenticationServiceException($repositoryProblem->getMessage(), 0, $repositoryProblem);
        }

        return $user;
    }
}
