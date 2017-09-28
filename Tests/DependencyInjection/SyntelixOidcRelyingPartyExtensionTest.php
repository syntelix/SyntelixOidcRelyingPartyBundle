<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Syntelix\Bundle\OidcRelyingPartyBundle\DependencyInjection\SyntelixOidcRelyingPartyExtension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SyntelixOidcRelyingPartyExtensionTest extends TestCase
{
    public function testDefault()
    {
        $container = new ContainerBuilder();
        $loader = new SyntelixOidcRelyingPartyExtension();
        $config = array($this->getFullConfig());
        $loader->load(array($this->getFullConfig()), $container);

        $definitionArray = array(
            'syntelix_oidc_rp.authentication.listener',
            'syntelix_oidc_rp.authentication.provider',
            'syntelix_oidc_rp.authentication.entrypoint',
            'syntelix_oidc_rp.validator.id_token',
            'syntelix_oidc_rp.http_client_response_handler',
            'syntelix_oidc_rp.jwk_handler',
            'syntelix_oidc_rp.helper.nonce',
            'syntelix_oidc_rp.user.provider',
            'syntelix_oidc_rp.abstract_resource_owner.generic',
            'buzz.client',
            'syntelix_oidc_rp.http_client',
            'syntelix_oidc_rp.resource_owner.generic',
        );

        foreach ($definitionArray as $definition) {
            $this->assertTrue($container->hasDefinition($definition));
        }

        $this->assertEquals('syntelix_oidc_rp', $loader->getAlias());
    }

    protected function getFullConfig()
    {
        $yaml = <<<EOF
base_url: http://base-url.com
client_id: my_client_id
client_secret: my_client_secret
issuer: http://issuer.com
token_ttl: 1
authentication_ttl: 2
ui_locales: FR_fr
display: page
prompt: login
scope: openid
endpoints_url:
    authorization: /auth
    token: /token
    userinfo: /userinfo
http_client:
    timeout: 3
    verify_peer: false
    max_redirects: 4
    ignore_errors: false
    proxy: localhost:8080
jwk_url: http://issuer.com/op.jwk
jwk_cache_ttl: 5
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @param mixed  $value
     * @param string $key
     */
    private function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->containerBuilder->getParameter($key), sprintf('%s parameter is correct', $key));
    }
}
