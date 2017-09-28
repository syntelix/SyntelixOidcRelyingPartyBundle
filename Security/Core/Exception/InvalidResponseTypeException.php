<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception;

use InvalidArgumentException;

/**
 * InvalidResponseTypeException.
 */
class InvalidResponseTypeException extends InvalidArgumentException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Response type used is unknow';
    }
}
