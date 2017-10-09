<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Nonce.
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class NonceHelperTest extends TestCase
{
    public function testBuildNonceValue()
    {
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');
        $session->expects($this->once())
                ->method('set')
                ->with($this->equalTo('auth.oic.test'), $this->anything());

        $nonceHelper = new NonceHelper($session, array('nonce' => true, 'state' => true));

        $nonce = $nonceHelper->buildNonceValue('amy', 'test');

        $this->assertInternalType('string', $nonce);
        $this->assertGreaterThan(1, strlen($nonce));
    }

    public function testBuildNonceValueGreaterThan255()
    {
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');
        $session->expects($this->once())
                ->method('set')
                ->with($this->equalTo('auth.oic.test'), $this->anything());

        $nonceHelper = new NonceHelper($session, array('nonce' => true, 'state' => true));

        $nonce = $nonceHelper->buildNonceValue(hash('SHA512', 'amy').hash('SHA512', 'amy'), 'test');

        $this->assertInternalType('string', $nonce);
        $this->assertGreaterThan(1, strlen($nonce));
    }

    public function testCheckStateAndNonceShouldBeValid()
    {
        $request = new Request();
        $request->query->set('state', 'value');
        $request->query->set('nonce', 'value');

        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');
        $session->expects($this->exactly(2))
                ->method('get')
                ->willReturn(serialize('value'));
        $nonceHelper = new NonceHelper($session, array('nonce' => true, 'state' => true));

        $nonceHelper->checkStateAndNonce($request);
    }

    /**
     * @expectedException \Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\InvalidNonceException
     */
    public function testCheckStateAndNonceShouldFail()
    {
        $request = new Request();
        $request->query->set('state', 'value');
        $request->query->set('nonce', 'value');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())
                ->method('get')
                ->willReturn(serialize('error'));
        $nonceHelper = new NonceHelper($session, array('nonce' => true, 'state' => true));

        $nonceHelper->checkStateAndNonce($request);
    }
}
