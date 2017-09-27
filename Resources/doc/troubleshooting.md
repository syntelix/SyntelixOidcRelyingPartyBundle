# Troubleshooting

## I get the error "Issuer does not match"

The Issuer Identifier for the OpenID Provider (which is typically obtained during Discovery) MUST exactly match the
value of the iss (issuer) Claim.

Look for typos or missing characters in the value of `issuer` set in your `app/config/config.yml`.