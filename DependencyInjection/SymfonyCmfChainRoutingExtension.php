<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
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

        $container->setParameter($this->getAlias().'.controllers_by_alias', $config['doctrine']['controllers_by_alias']);
        $container->setParameter($this->getAlias().'.route_entity_class', $config['doctrine']['route_entity_class']);

        $loader->load('chain_routing.xml');
        if ($config['chain']['replace_symfony_router']) {
            $container->setAlias('router', 'symfony_cmf_chain_routing.router');
        }

        // not used unless in the routers_by_id map
        $loader->load('cmf_routing.xml');

        $router = $container->getDefinition('symfony_cmf_chain_routing.router');
        foreach($config['chain']['routers_by_id'] as $id => $priority) {
            $router->addMethodCall('add', array(new Reference($id), $priority));
        }
    }

    /**
     * @return string
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/';
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return 'http://www.example.com/symfony/schema/';
    }
}
