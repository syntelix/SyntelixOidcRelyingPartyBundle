<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\ResourceOwner;

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
