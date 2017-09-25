<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Tests\Security\Http\EntryPoint;

use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Http\EntryPoint\OICEntryPoint;

/**
 * OICEntryPoint
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class OICEntryPointTest extends \PHPUnit_Framework_TestCase
{
     public function testStart()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $httpUtils = $this->getMock('Symfony\Component\Security\Http\HttpUtils');
        $httpUtils->expects($this->once())
                ->method("createRedirectResponse")
                ->with($this->equalTo($request), $this->equalTo("someUri"))
                ->willReturn("realUri")
                ;

        $ResourceOwner = $this->getMock('Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\ResourceOwnerInterface');
        $ResourceOwner->expects($this->once())
                ->method("getAuthenticationEndpointUrl")
                ->with($this->equalTo($request))
                ->willReturn("someUri")
                ;
                
        $entryPoint = new OICEntryPoint($httpUtils, $ResourceOwner);
                
        $response = $entryPoint->start($request, null);

        $this->assertEquals('realUri', $response);
    }
}
