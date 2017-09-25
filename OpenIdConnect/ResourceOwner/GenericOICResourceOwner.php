<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\ResourceOwner;

use Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\ResourceOwner\AbstractGenericOICResourceOwner;

/**
 * GenericOICResourceOwner
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class GenericOICResourceOwner extends AbstractGenericOICResourceOwner
{

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return "generic";
    }
}
