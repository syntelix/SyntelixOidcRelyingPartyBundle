<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception;

use InvalidArgumentException;

/**
 * InvalidAuthorizationCodeException.
 */
class InvalidIdTokenException extends InvalidArgumentException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Invalid ID Token.';
    }
}
