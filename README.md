[![Build Status](https://travis-ci.org/syntelix/SyntelixOidcRelyingPartyBundle.svg?branch=master)](https://travis-ci.org/syntelix/SyntelixOidcRelyingPartyBundle)
[![Latest Stable Version](https://poser.pugx.org/syntelix/oidc-relying-party-bundle/v/stable)](https://packagist.org/packages/syntelix/oidc-relying-party-bundle)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/afb6df43-fad0-4007-a1f2-7643a44b010d/small.png)](https://insight.sensiolabs.com/projects/afb6df43-fad0-4007-a1f2-7643a44b010d)

# SyntelixOidcRelyingPartyBundle

This bundle is a fork of https://github.com/waldo2188/OpenIdConnectRelyingPartyBundle.

SyntelixOidcRelyingPartyBundle is an implementation of [OpenID Connect Specification](http://openid.net/specs/openid-connect-basic-1_0.html).

## Requirements
- Symfony 3.2+

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

## What is next?

- [Read the documentation](Resources/doc/index.md)
