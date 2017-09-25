<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception;


/**
 * UnsuportedGrantTypeException
 *
 */
class UnsuportedGrantTypeException extends \InvalidArgumentException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Grant type used is not supported';
    }
}
