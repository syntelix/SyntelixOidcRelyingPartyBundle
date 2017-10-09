<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Security\Http\Logout;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * OpenId Connect Logout.
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class OICLogout
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $options;

    /**
     * @param array                 $options
     * @param TokenStorageInterface $tokenStorage
     * @param HttpUtils             $httpUtils
     * @param RouterInterface       $router
     */
    public function __construct(array $options, TokenStorageInterface $tokenStorage, HttpUtils $httpUtils, RouterInterface $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->httpUtils = $httpUtils;
        $this->router = $router;
        $this->options = $options;
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        $request->getSession()->clear();
        $request->getSession()->invalidate();
        $this->tokenStorage->setToken(null);

        $redirectResponse = new RedirectResponse($this->getRedirectAfterLogoutURI($request));

        if ($request->server->get('HTTP_REFERRER') != $this->httpUtils->generateUri($request, '_oic_rp_logout')) {
            $redirectResponse = new RedirectResponse($this->getRedirectAfterLogoutURI($request));
        }

        if (array_key_exists('logout', $this->options['endpoints_url'])) {
            $redirectResponse = new RedirectResponse($this->getOIDCLogoutEndPointURI($request));
        }

        return $redirectResponse;
    }

    /**
     * @param Request $request
     *
     * @return mixed|string
     */
    private function getRedirectAfterLogoutURI(Request $request)
    {
        if ($this->router->getRouteCollection()->get($this->options['redirect_after_logout'])) {
            return $this->httpUtils->generateUri($request, $this->options['redirect_after_logout']);
        }

        return $this->options['redirect_after_logout'];
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getOIDCLogoutEndPointURI(Request $request)
    {
        return $this->options['endpoints_url']['logout']
                    .'?post_logout_redirect_uri='
                    .urlencode($this->getRedirectAfterLogoutURI($request));
    }
}
