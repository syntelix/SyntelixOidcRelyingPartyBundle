# Configuration

## Set up yout OpenID Connect Provider

You will need a working OpenID Connect Identity Provider in order to use the bundle. For testing purposes, you can use
the [MIT ITC Demo Server](https://mitreid.org). The following examples are based on this public service, but remember
to create a client, write down its ID and secret and to enable the `openid`, `email` and `profile` scopes for it.

Authentication between the RP and OP is done through HTTP Basic authentication, so configure your OP properly.

## Configure the bundle

```yaml
#/app/config/config.yml

syntelix_oic_rp:
    http_client:                     # Configuration for Buzz
        timeout: 5
        verify_peer: ~
        max_redirects: 5
        proxy: ~
    base_url: http://localhost:8000
    client_id: my_client_id          # OpenID Connect client id given by the OpenId Connect Provider
    client_secret: my_client_secret  # OpenID Connect client secret given by the OpenId Connect Provider
    issuer: https://mitreid.org/     # URL of the OpenID Connect Provider
    endpoints_url:                   # Part of the URL of the OpenID Connect Provider
        authorization: /authorize
        token: /token
        userinfo: /userinfo
        logout: /logout
    display: page                    # How the authentication form will be display to the end user
    scope: openid profile email      # List of the scopes you need
    authentication_ttl: 300          # Maximum age of the authentication
    token_ttl: 300                   # Maximum age for tokenID
    jwk_url: https://mitreid.org/jwk # URL to the JSON Web Key of OpenID Connect Provider
    jwk_cache_ttl: 86400             # Validity periods in second where the JWK store in cache is valid
    enabled_state: true              # Enable the use of the state value. This is useful for mitigate replay attack
    enabled_nonce: true              # Enable the use of the nonce value. This is useful for mitigate replay attack
    enduserinfo_request_method: POST # Define the method (POST, GET) used to request the Enduserinfo Endpoint of the OIDC Provider
    redirect_after_logout: /         # URI or route name used for redirect user after a logout
```

## Configure routing

```yaml
#/app/config/routing.yml

# Syntelix OpenID Connect Relying Party
_oic_rp:
    resource: "@SyntelixOidcRelyingPartyBundle/Resources/config/routing.yml"

# Set a path for the route name 'login_check'
# You don't need to provide a controller for this route
login_check:
    path: /login_check
```

## Configure security

It is recommended to set a path for `default_target_path` in order to prevent redirection loops.
You should also set a path for `login_path`, the same value as `default_target_path` is a good start.

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
