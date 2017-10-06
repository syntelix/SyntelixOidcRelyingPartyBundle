<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Tests\Security\Core\Authentication\Provider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\ResourceOwnerInterface;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Authentication\Provider\OICProvider;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Authentication\Token\OICToken;

/**
 * OICProvider.
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class OICProviderTest extends TestCase
{
    public function testAuthenticateShouldReturnToken()
    {
        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);

        $user = $this->createMock(UserInterface::class);
        $user->expects($this->once())
                ->method('getUsername')
                ->willReturn('amy.pond');
        $user->expects($this->once())
                ->method('getRoles')
                ->willReturn(array('ROLE_FAKE'));

        $userProvider = $this->createMock(UserProviderInterface::class);
        $userProvider->expects($this->once())
                ->method('loadUserByUsername')
                ->with($this->equalTo('amy.pond'))
                ->willReturn($user);

        $token = $this->createMock(OICToken::class);
        $token->expects($this->exactly(2))
                ->method('getUsername')
                ->willReturn('amy.pond');

        $claims = new \stdClass();
        $claims->claims = array('sub' => 'username');

        $tokenValue = array(
            'getAccessToken' => 'access',
            'getIdToken' => $claims,
            'getRefreshToken' => 'refresh',
            'getUser' => 'user',
        );
        foreach ($tokenValue as $methode => $returnValue) {
            $token->expects($this->any())
                    ->method($methode)
                    ->willReturn($returnValue);
        }

        $oicProvider = new OICProvider($userProvider, $resourceOwner);

        $resultToken = $oicProvider->authenticate($token);

        $this->assertEquals($tokenValue['getAccessToken'], $resultToken->getAccessToken());
        $this->assertEquals($tokenValue['getRefreshToken'], $resultToken->getRefreshToken());
        $this->assertEquals($tokenValue['getIdToken'], $resultToken->getIdToken());
        $this->assertInstanceOf(UserInterface::class, $resultToken->getUser());
        $this->assertCount(1, $resultToken->getRoles());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testAuthenticationShouldFailed()
    {
        $resouceOwner = $this->createMock("Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\ResourceOwnerInterface");

        $user = $this->createMock('Symfony\Component\Security\Core\User\UserInterface');
        $user->expects($this->once())
                ->method('getUsername')
                ->willReturn('amy.pond');

        $userProvider = $this->createMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $userProvider->expects($this->once())
                ->method('loadUserByUsername')
                ->willReturn($user);

        $token = $this->createMock('Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Authentication\Token\OICToken');
        $token->expects($this->exactly(2))
                ->method('getUsername')
                ->willReturn('rory.willialms');

        $oicProvider = new OICProvider($userProvider, $resouceOwner);

        $resultToken = $oicProvider->authenticate($token);
    }
}
