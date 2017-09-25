<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception;

/**
 * InvalidAuthorizationCodeException
 *
 */
class InvalidAuthorizationCodeException extends \InvalidArgumentException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Invalid authorization code or no such code.';
    }
}
