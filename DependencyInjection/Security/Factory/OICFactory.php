<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * OICFactory.
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class OICFactory extends AbstractFactory
{
    /**
     * @param NodeDefinition $node
     */
    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);

        $node->children()
            ->scalarNode('create_users')->defaultFalse()->end()
            ->arrayNode('created_users_roles')
                ->treatNullLike(array())
                ->beforeNormalization()
                    ->ifTrue(function ($v) {
                        return !is_array($v);
                    })
                    ->then(function ($v) {
                        return array($v);
                    })
                ->end()
                ->prototype('scalar')->end()
                ->defaultValue(array('ROLE_OIC_USER'))->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'security.authentication.provider.oic_rp.'.$id;

        $container
                ->setDefinition($providerId, new ChildDefinition('syntelix_oic_rp.authentication.provider'))
                ->addArgument(new Reference($userProviderId))
                ->addArgument(new Reference('syntelix_oic_rp.resource_owner.generic'))
                ->addArgument($config['create_users'])
                ->addArgument($config['created_users_roles'])
        ;

        return $providerId;
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId = 'security.authentication.entrypoint.oic_rp.'.$id;

        $container
            ->setDefinition($entryPointId, new ChildDefinition('syntelix_oic_rp.authentication.entrypoint'))
            ->addArgument(new Reference('syntelix_oic_rp.resource_owner.generic'))
        ;

        return $entryPointId;
    }

    /**
     * {@inheritdoc}
     */
    protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId = parent::createListener($container, $id, $config, $userProvider);

        $container
                ->getDefinition($listenerId)
                ->addMethodCall('setResourceOwner', array(new Reference('syntelix_oic_rp.resource_owner.generic')))
                ->addMethodCall('setTokenStorage', array(new Reference('security.token_storage')))
                ->addMethodCall('setAuthorizationChecker', array(new Reference('security.authorization_checker')))
        ;

        return $listenerId;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListenerId()
    {
        return 'syntelix_oic_rp.authentication.listener';
    }

    /**
     * {@inheritdoc}
     * Allow to add a custom configuration in a firewall's configuration
     * in the security.yml file.
     */
    public function getKey()
    {
        return 'openidconnect';
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }
}
