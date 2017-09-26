<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Nonce
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class NonceHelperTest extends TestCase
{
    public function testBuildNonceValue()
    {
        $ession = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');
        $ession->expects($this->once())
                ->method('set')
                ->with($this->equalTo("auth.oic.test"), $this->anything());
        
        $nonceHelper = new NonceHelper($ession, array("nonce" => true, "state" => true));
        
        $nonce = $nonceHelper->buildNonceValue("amy", 'test');
                
        $this->assertTrue(is_string($nonce));
        $this->assertGreaterThan(1, strlen($nonce));
    }
    
    public function testBuildNonceValueGreaterThan255()
    {
        $ession = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');
        $ession->expects($this->once())
                ->method('set')
                ->with($this->equalTo("auth.oic.test"), $this->anything());
        
        $nonceHelper = new NonceHelper($ession, array("nonce" => true, "state" => true));
        
        $nonce = $nonceHelper->buildNonceValue(hash('SHA512', "amy") . hash('SHA512', "amy"), 'test');
                
        $this->assertTrue(is_string($nonce));
        $this->assertGreaterThan(1, strlen($nonce));
    }
    
    public function testCheckStateAndNonceShouldBeValid()
    {
        $request = new Request();
        $request->query->set('state', 'unevaleur');
        $request->query->set('nonce', 'unevaleur');
        
        $ession = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');
        $ession->expects($this->exactly(2))
                ->method('get')
                ->willReturn(serialize('unevaleur'));
        $nonceHelper = new NonceHelper($ession, array("nonce" => true, "state" => true));
        
        $nonceHelper->checkStateAndNonce($request);
    }
    
    /**
     * @expectedException Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\InvalidNonceException
     */
    public function testCheckStateAndNonceShouldFail()
    {
        $request = new Request();
        $request->query->set('state', 'unevaleur');
        $request->query->set('nonce', 'unevaleur');
        
        $ession = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');
        $ession->expects($this->once())
                ->method('get')
                ->willReturn(serialize('error'));
        $nonceHelper = new NonceHelper($ession, array("nonce" => true, "state" => true));
        
        $nonceHelper->checkStateAndNonce($request);
    }
}
