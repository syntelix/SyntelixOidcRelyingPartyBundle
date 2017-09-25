<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception;

/**
 * InvalidRequestException
 *
 */
class InvalidRequestException extends \InvalidArgumentException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Invalide request';
    }
}
