<?php

/*
 * This file is part of the SyntelixOidcRelayingPartyBundle package.
 */

namespace Syntelix\Bundle\OidcRelyingPartyBundle;

use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Syntelix\Bundle\OidcRelyingPartyBundle\DependencyInjection\Security\Factory\OICFactory;
use Syntelix\Bundle\OidcRelyingPartyBundle\DependencyInjection\SyntelixOidcRelyingPartyExtension;

class SyntelixOidcRelyingPartyBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new OICFactory());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        // return the right extension instead of "auto-registering" it. Now the
        // alias can be syntelix_oic_rp instead of syntelix_open_id_connect_relying_party..
        if (null === $this->extension) {
            return new SyntelixOidcRelyingPartyExtension();
        }

        return $this->extension;
    }
}
