<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception;

/**
 * InvalidClientOrSecretException.
 */
class InvalidClientOrSecretException extends \InvalidArgumentException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Invalid client_id or client_secret.';
    }
}
