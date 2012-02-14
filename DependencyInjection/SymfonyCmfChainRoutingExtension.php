<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @author Philippo de Santis
 * @author David Buchmann
 */
class SymfonyCmfChainRoutingExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // TODO move this to the Configuration class as soon as it supports setting such a default
        array_unshift($configs,
            array('chain' => array(
                'routers_by_id' => array(
                    'router.default' => 100,
                ),
            ),
        ));

        $processor = new Processor();
        $configuration = new Configuration();
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $config = $processor->processConfiguration($configuration, $configs);

        if (!empty($config['doctrine']['enabled'])) {
            $this->setupDoctrineRouter($config['doctrine'], $container, $loader);
        }

        /* set up the chain router */
        $loader->load('chain_routing.xml');
        // only replace the default router by overwriting the 'router' alias if config tells us to
        if ($config['chain']['replace_symfony_router']) {
            $container->setAlias('router', 'symfony_cmf_chain_routing.router');
        }

        // add the routers defined in the configuration mapping
        $router = $container->getDefinition('symfony_cmf_chain_routing.router');
        foreach ($config['chain']['routers_by_id'] as $id => $priority) {
            $router->addMethodCall('add', array(new Reference($id), $priority));
        }
    }

    /**
     * Set up the DoctrineRouter - only to be called if enabled is set to true
     *
     * @param $config the compiled configuration for the doctrine router
     * @param $container the container builder
     * @param $loader the configuration loader
     */
    private function setupDoctrineRouter($config, $container, $loader)
    {
        $container->setParameter($this->getAlias() . '.generic_controller', $config['generic_controller']);
        $container->setParameter($this->getAlias() . '.controllers_by_alias', $config['controllers_by_alias']);
        $container->setParameter($this->getAlias() . '.controllers_by_class', $config['controllers_by_class']);
        $container->setParameter($this->getAlias() . '.templates_by_class', $config['templates_by_class']);

        $loader->load('cmf_routing.xml');
        $container->setParameter($this->getAlias() . '.routing_repositoryroot', $config['routing_repositoryroot']);

        $doctrine = $container->getDefinition($this->getAlias() . '.doctrine_router');
        $doctrine->replaceArgument(1, new Reference($config['route_repository_service']));

        // if any mappings are defined, set the respective resolvers
        if (!empty($config['generic_controller'])) {
            $doctrine->addMethodCall('addControllerResolver', array(new Reference($this->getAlias() . '.resolver_explicit_template')));
        }
        if (!empty($config['controllers_by_alias'])) {
            $doctrine->addMethodCall('addControllerResolver', array(new Reference($this->getAlias() . '.resolver_controllers_by_alias')));
        }
        if (!empty($config['controllers_by_class'])) {
            $doctrine->addMethodCall('addControllerResolver', array(new Reference($this->getAlias() . '.resolver_controllers_by_class')));
        }

        if (!empty($config['generic_controller']) && !empty($config['templates_by_class'])) {
            $doctrine->addMethodCall('addControllerResolver', array(new Reference($this->getAlias() . '.resolver_templates_by_class')));
        }
    }
}
