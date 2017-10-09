<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\Tests\Constraint;

use PHPUnit\Framework\TestCase;
use Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\Constraint\IDTokenValidator;

/**
 * IDTokenValidatorTest.
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class IDTokenValidatorTest extends TestCase
{
    private $options = array(
        'issuer' => 'anIssuer',
        'client_id' => 'a_client_id',
        'token_ttl' => 3600,
        'authentication_ttl' => 3600,
    );
    private $token;

    public function setUp()
    {
        $this->token = array(
            'claims' => array(
                'iss' => 'anIssuer',
                'aud' => 'a_client_id',
                'azp' => 'a_client_id',
                'exp' => (time() + 3600),
                'iat' => time(),
                'auth_time' => time(),
            ),
        );
    }

    public function testAllShouldBeGood()
    {
        $validator = new IDTokenValidator($this->options);

        $validator->setIdToken($this->token);

        $res = $validator->isValid();
        $this->assertTrue($res);
    }

    public function testAllShouldBeGoodWithoutTime()
    {
        $this->options['authentication_ttl'] = null;
        $validator = new IDTokenValidator($this->options);

        $validator->setIdToken($this->token);

        $res = $validator->isValid();
        $this->assertTrue($res);
    }

    public function testAllShouldNotFailWithoutTime()
    {
        unset($this->token['claims']['auth_time']);

        $validator = new IDTokenValidator($this->options);

        $validator->setIdToken($this->token);

        $res = $validator->isValid();
        $this->assertTrue($res);
    }

    public function testAllShouldBeGoodAdd()
    {
        $this->token['claims']['aud'] = array('a_client_id', 'a_client_id2');

        $validator = new IDTokenValidator($this->options);

        $validator->setIdToken($this->token);

        $res = $validator->isValid();
        $this->assertTrue($res);
    }

    public function testAllShouldBeGoodAddSecond()
    {
        $this->token['claims']['aud'] = array('a_client_id');

        $validator = new IDTokenValidator($this->options);

        $validator->setIdToken($this->token);

        $res = $validator->isValid();
        $this->assertTrue($res);
    }

    public function testAllShouldFailAtIssuer()
    {
        $this->options['issuer'] = 'fake';
        $validator = new IDTokenValidator($this->options);

        $validator->setIdToken($this->token);

        $res = $validator->isValid();
        $this->assertFalse($res);
    }

    public function testAllShouldFailAtClient()
    {
        $this->token['claims']['aud'] = new IDTokenValidator($this->options);
        $this->token['claims']['azp'] = new IDTokenValidator($this->options);
        $validator = new IDTokenValidator($this->options);

        $validator->setIdToken($this->token);

        $res = $validator->isValid();
        $this->assertFalse($res);
    }

    public function testAllShouldFailAtAzp()
    {
        $this->token['claims']['aud'] = array('a_client_id', 'a_client_id2');
        unset($this->token['claims']['azp']);
        $validator = new IDTokenValidator($this->options);

        $validator->setIdToken($this->token);

        $res = $validator->isValid();
        $this->assertFalse($res);
    }
}
