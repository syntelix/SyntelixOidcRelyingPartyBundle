# OpenID Connect Relying Party Bundle

1. [Installation](installation.md)
1. [Configuration](configuration.md)
1. [Troubleshooting](troubleshooting.md)

## What is the link for login end user?

There are two ways to trigger user authentication.
- When an end user requests a resource who is behind a firewall, he will be automatically redirected to the
OP's login page.
- Accessing the route configured for `login_check`.

## How to display a logout link?

The name of the logout route is `_oic_rp_logout`. You can use it in your Twig template like below:

```twig
<a href="{{ path('_oic_rp_logout') }}">Logout</a>
```

If you have specified a logout endpoint, the logout mechanism will proceed of the logout the user on the endpoint.

## Not yet implemented

### TODO
 - Add re-authentication mechanise

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
