<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\ResourceOwner;

/**
 * GenericOICResourceOwner.
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class GenericOICResourceOwner extends AbstractGenericOICResourceOwner
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'generic';
    }
}
