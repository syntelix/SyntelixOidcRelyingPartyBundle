<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\Tests\ResourceOwner;

use Buzz\Client\AbstractCurl;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Http\HttpUtils;
use Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\Constraint\ValidatorInterface;
use Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\NonceHelper;
use Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\ResourceOwner\GenericOICResourceOwner;
use Symfony\Component\HttpFoundation\Request;
use Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\Response\OICResponseHandler;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Authentication\Token\OICToken;

/**
 * GenericOICResourceOwner.
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class AbstractGenericOICResourceOwnerTest extends TestCase
{
    public function testShouldAuthenticationEndpointUrl()
    {
        $resourceOwner = $this->createGenericOICResourceOwner('http://localhost/login_check');
        $request = new Request();

        $expected = 'http://oic.com/auth?client_id=my_client_id&display=page&max_age=300&redirect_uri=http%3A%2F%2Flocalhost%2Flogin_check&response_type=code&scope=openid%20profile%20other&ui_locales=F_fr';
        $res = $resourceOwner->getAuthenticationEndpointUrl($request, 'plop_uri', array('display' => 'page'));

        $this->assertEquals($expected, $res);
    }

    public function testShouldReturnTokenEndpointUrl()
    {
        $resourceOwner = $this->createGenericOICResourceOwner();

        $this->assertEquals('http://oic.com/token', $resourceOwner->getTokenEndpointUrl());
    }

    public function testShouldReturnUserinfoEndpointUrl()
    {
        $resourceOwner = $this->createGenericOICResourceOwner();

        $this->assertEquals('http://oic.com/userinfo', $resourceOwner->getUserinfoEndpointUrl());
    }

    public function testShouldAuthenticateUser()
    {
        $responseHandler = $this->getMockBuilder(OICResponseHandler::class)
                ->disableOriginalConstructor()->getMock();

        $jwt = new \JOSE_JWT(array('sub' => 'amy.pond'));

        $responseHandler->expects($this->once())
                ->method('handleTokenAndAccessTokenResponse')
                ->willReturn(array(
                    'access_token' => 'access_token_value',
                    'refresh_token' => 'refresh_token_value',
                    'expires_in' => 'expires_in_value',
                    'id_token' => $jwt,
        ));

        $resourceOwner = $this->createGenericOICResourceOwner(null, true, $responseHandler);

        $request = new Request();
        $request->query->set('code', 'anOicCode');

        $res = $resourceOwner->authenticateUser($request);

        $this->assertInstanceOf(OICToken::class, $res);
        $this->assertEquals('amy.pond', $res->getUsername());
    }

    /**
     * @expectedException \Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\InvalidIdTokenException
     */
    public function testShouldFailAuthenticateUser()
    {
        $resourceOwner = $this->createGenericOICResourceOwner(null, false);

        $request = new Request();
        $request->query->set('code', 'anOicCode');

        $resourceOwner->authenticateUser($request);
    }

    /**
     * @expectedException \Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\InvalidRequestException
     * @expectedExceptionMessage no such access_token
     */
    public function testShouldFailAuthenticateUserNoSuchAccessToken()
    {
        $responseHandler = $this->getMockBuilder(OICResponseHandler::class)
                ->disableOriginalConstructor()->getMock();

        $resourceOwner = $this->createGenericOICResourceOwner(null, true, $responseHandler);

        $oicToken = new OICToken();

        $resourceOwner->getEndUserinfo($oicToken);
    }

    /**
     * @expectedException \Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\InvalidIdTokenException
     * @expectedExceptionMessage The sub value is not equal
     */
    public function testShouldFailAuthenticateUserSubValueNotEqual()
    {
        $responseHandler = $this->getMockBuilder(OICResponseHandler::class)
                ->disableOriginalConstructor()->getMock();

        $resourceOwner = $this->createGenericOICResourceOwner(null, true, $responseHandler);

        $claims = new \stdClass();
        $claims->claims = array('sub' => 'username');

        $oicToken = new OICToken();
        $oicToken->setAccessToken('plop');
        $oicToken->setIdToken($claims);

        $resourceOwner->getEndUserinfo($oicToken);
    }

    public function testShouldReturnName()
    {
        $resourceOwner = $this->createGenericOICResourceOwner();

        $this->assertEquals('generic', $resourceOwner->getName());
    }

    private function createGenericOICResourceOwner(
            $httpUtilsRV = '',
            $idTokenValidatorRV = true,
            $responseHandler = null)
    {
        $httpUtils = $this->getMockBuilder(HttpUtils::class)
                ->disableOriginalConstructor()->getMock();
        $httpUtils->expects($this->atMost(2))
                ->method('generateUri')
                ->willReturn($httpUtilsRV);

        $httpClient = $this->getMockBuilder(AbstractCurl::class)
                ->disableOriginalConstructor()->getMock();

        $idTokenValidator = $this->getMockBuilder(ValidatorInterface::class)
                ->disableOriginalConstructor()->getMock();
        $idTokenValidator->expects($this->any())
                ->method('isValid')
                ->willReturn($idTokenValidatorRV);
        $idTokenValidator->expects($this->any())
                ->method('getErrors')
                ->willReturn(array());

        $responseHandler = $responseHandler ? $responseHandler : $this->getMockBuilder(OICResponseHandler::class)
                ->disableOriginalConstructor()->getMock();

        $nonceHelper = $this->getMockBuilder(NonceHelper::class)
                ->disableOriginalConstructor()->getMock();

        return new GenericOICResourceOwner(
                $httpUtils,
                $httpClient,
                $idTokenValidator,
                $responseHandler,
                $nonceHelper,
                array(
                    'client_id' => 'my_client_id',
                    'client_secret' => 'my_client_secret',
                    'scope' => 'openid profile other',
                    'authentication_ttl' => '300',
                    'ui_locales' => 'F_fr',
                    'enduserinfo_request_method' => 'POST',
                    'endpoints_url' => array(
                        'authorization' => 'http://oic.com/auth',
                        'token' => 'http://oic.com/token',
                        'userinfo' => 'http://oic.com/userinfo',
                        ),
                    )
                );
    }
}
