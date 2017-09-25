<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception;

/**
 * InvalidIdSignatureException
 *
 */
class InvalidIdSignatureException extends \InvalidArgumentException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Invalid signature Token.';
    }
}
