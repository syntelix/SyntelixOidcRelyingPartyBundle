<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect;

use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Authentication\Token\OICToken;
use Symfony\Component\HttpFoundation\Request;

/**
 * ResourceOwnerInterface
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
interface ResourceOwnerInterface
{

	/**
	 * Returns the provider's authorization url
	 *
	 * @param Request $request
	 * @param string $redirectUri The uri to redirect the client back to
	 * @param array $extraParameters An array of parameters to add to the url
	 *
	 * @return string The authorization url
	 */
    public function getAuthenticationEndpointUrl(Request $request, $redirectUri = null, array $extraParameters = array());

    /**
     *
     * @return string The token endpoint url
     */
    public function getTokenEndpointUrl();

    /**
     * @return string The userinfo endpoint url
     */
    public function getUserinfoEndpointUrl();

    /**
     * Use the code parameter set in request query for retrieve the enduser informations
     *
     * @param Request $request
     *
     * @return OICToken
     */
    public function authenticateUser(Request $request);
    
    /**
     * Call the OpenId Connect Provider to get userinfo against an access_token
     *
     * @see http://openid.net/specs/openid-connect-basic-1_0.html#UserInfo
     *
     * @param OICToken $oicToken
     *
     * @return array
     */
    public function getEndUserinfo(OICToken $oicToken);

    /**
     * Return a name for the resource owner.
     *
     * @return string
     */
    public function getName();
}
