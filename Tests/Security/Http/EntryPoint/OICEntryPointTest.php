<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Tests\Security\Http\EntryPoint;

use PHPUnit\Framework\TestCase;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Http\EntryPoint\OICEntryPoint;

/**
 * OICEntryPoint.
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class OICEntryPointTest extends TestCase
{
    public function testStart()
    {
        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');

        $httpUtils = $this->createMock('Symfony\Component\Security\Http\HttpUtils');
        $httpUtils->expects($this->once())
                ->method('createRedirectResponse')
                ->with($this->equalTo($request), $this->equalTo('someUri'))
                ->willReturn('realUri')
                ;

        $ResourceOwner = $this->createMock('Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\ResourceOwnerInterface');
        $ResourceOwner->expects($this->once())
                ->method('getAuthenticationEndpointUrl')
                ->with($this->equalTo($request))
                ->willReturn('someUri')
                ;

        $entryPoint = new OICEntryPoint($httpUtils, $ResourceOwner);

        $response = $entryPoint->start($request, null);

        $this->assertEquals('realUri', $response);
    }
}
