# OpenID Connect Relying Party Bundle

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require syntelix/oidc-relying-party-bundle dev-master
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Syntelix\Bundle\OidcRelyingPartyBundle\SyntelixOidcRelyingPartyBundle(),
        );

        // ...
    }

    // ...
}
```

## Configuration

Configure the bundle.

```yaml
#/app/config/config.yml

syntelix_oic_rp:
    http_client:                     # Configuration for Buzz
        timeout: 5
        verify_peer: ~
        max_redirects: 5
        proxy: ~
    base_url: http://my-web-site.tld/
    client_id: my_client_id          # OpenID Connect client id given by the OpenId Connect Provider
    client_secret: my_client_secret  # OpenID Connect client secret given by the OpenId Connect Provider
    issuer: https://openid-connect-provider.tld # URL of the OpenID Connect Provider
    endpoints_url:                   # Part of the URL of the OpenID Connect Provider
        authorization: /auth
        token: /token
        userinfo: /userinfo
        logout: /logout
    display: page                    # How the authentication form will be display to the enduser
    scope: openid profile email address phone # List of the scopes you need
    authentication_ttl: 300          # Maximum age of the authentication
    token_ttl: 300                   # Maximum age for tokenID
    jwk_url: https://openid-connect-provider.tld/op.jwk # URL to the JSON Web Key of OpenID Connect Provider
    jwk_cache_ttl: 86400             # Validity periods in second where the JWK store in cache is valid
    enabled_state: true              # Enable the use of the state value. This is useful for mitigate replay attack
    enabled_nonce: true              # Enable the use of the nonce value. This is useful for mitigate replay attack
    enduserinfo_request_method: POST # Define the method (POST, GET) used to request the Enduserinfo Endpoint of the OIDC Provider
    redirect_after_logout: /home     # URI or route name used for redirect user after a logout
```

Configure routing.

```yaml
#/app/config/routing.yml
_oic_rp:
    resource: "@SyntelixOpenIdConnectRelyingPartyBundle/Resources/config/routing.yml"

# Set a path for the route name 'login_check'
# You don't need to provide a controller for this route
login_check:
    path: /login_check
```

Configure security.

I recommend you to set a path for `default_target_path`. Because you risk to suffer redirection loop.
You must maybe set a path for `login_path`, the same as `default_target_path`, is a good start.

```yaml
#/app/config/security.yml
security:
    providers:
        OICUserProvider: 
            id: syntelix_oic_rp.user.provider
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        secured_area:
            pattern: ^/
            anonymous: ~
            openidconnect:
                always_use_default_target_path: false
                default_target_path: /private-page
                login_path: /private-page
                target_path_parameter: ~
                use_referer: ~
                create_users: true                 # Create user if not found
                created_users_roles: ROLE_OIC_USER # Add this role(s) to new User
    access_control:
        - { path: ^/private-page, roles: ROLE_OIC_USER }
        - { path: ^/login$, roles: IS_AUTHENTICATED_ANONYMOUSLY }
```


## What is the link for login end user?

Two way to authenticate user.
- The first, do nothing. When an end user come on a page who is behind a firewall,
he will be automatically  redirected to the OpenId Connect Provider's login page
- The second. You can create a login link with the route 'login_check'


## How to display a logout link?

The name of the logout route is `_oic_rp_logout`. You can use it in your Twig template like below:

```twig
<a href="{{ path('_oic_rp_logout') }}">Logout</a>
```
If you have specified a logout endpoint, the logout mechanism will proceed of the logout the user on the endpoint.


## TODO
 - Add re-authentication mechanise

## Not yet implemented
### Client Prepares Authentication Request

http://openid.net/specs/openid-connect-basic-1_0.html#AuthenticationRequest

This options parameter need to be implemented
 - claims_locales
 - id_token_hint
 - login_hint
 - acr_values


### ID Token Validation 

http://openid.net/specs/openid-connect-basic-1_0.html#IDTokenValidation

The point 7 is not implemented.
> If the acr Claim was requested, the Client SHOULD check that the asserted Claim 
> Value is appropriate. The meaning and processing of acr Claim Values is out of 
> scope for this document.

