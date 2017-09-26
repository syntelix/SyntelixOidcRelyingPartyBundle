<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Test\Security\Core\User;

use PHPUnit\Framework\TestCase;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\User\OICUserProvider;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\User\OICUser;

/**
 * OICUserProviderTest
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class OICUserProviderTest extends TestCase
{
    public function testLoadUserByUsername()
    {
        $username = 'amy.pond';
        
        $oicUser = $this->getMockBuilder('Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\User\OICUser')
                ->disableOriginalConstructor()->getMock();
        $oicUser->expects($this->exactly(2))
                ->method("getUsername")
                ->willReturn($username);
                
        $session = $this->createMock("Symfony\Component\HttpFoundation\Session\Session");
        $session->expects($this->once())
                ->method("has")
                ->willReturn(true);
        $session->expects($this->once())
                ->method("get")
                ->willReturn($oicUser);

        $oicUserProvider = new OICUserProvider($session);
        
        $oicUser = $oicUserProvider->loadUserByUsername($username);

        $this->assertInstanceOf(OICUser::class, $oicUser);
        $this->assertEquals($username, $oicUser->getUsername());
    }

    public function testRefreshUser()
    {
        $oicUser = new OICUser('amy.pond');

        $session = $this->createMock("Symfony\Component\HttpFoundation\Session\Session");
        $session->expects($this->once())
                ->method("has")
                ->willReturn(true);
        $session->expects($this->once())
                ->method("get")
                ->willReturn($oicUser);
        
        $oicUserProvider = new OICUserProvider($session);
        
        $oicUserReturn = $oicUserProvider->refreshUser($oicUser);

        $this->assertEquals($oicUser, $oicUserReturn);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshUsershouldFail()
    {
        $oicUser = $this->createMock('Symfony\Component\Security\Core\User\UserInterface');
        
        $session = $this->createMock("Symfony\Component\HttpFoundation\Session\Session");
        
        $oicUserProvider = new OICUserProvider($session);

        $oicUserProvider->refreshUser($oicUser);
    }
}
