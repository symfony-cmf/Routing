<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
* This class contains the configuration information for the bundle
*
* This information is solely responsible for how the different configuration
* sections are normalized, and merged.
*
* @author David Buchmann
*/
class Configuration implements ConfigurationInterface
{
    /**
     * Returns the config tree builder.
     *
     * @return \Symfony\Component\DependencyInjection\Configuration\NodeInterface
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('symfony_cmf_chain_routing')
            ->children()
                ->arrayNode('chain')
                    ->children()
                        ->arrayNode('routers_by_id')
                            ->useAttributeAsKey('id')
                            ->prototype('scalar')
                        ->end()->end()
                        ->scalarNode('replace_symfony_router')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('doctrine')
                    ->children()
                        ->scalarNode('generic_controller')->defaultValue('symfony_cmf_content.controller:indexAction')->end()
                        ->arrayNode('controllers_by_alias')
                            ->useAttributeAsKey('alias')
                            ->prototype('scalar')
                        ->end()->end()
                        ->arrayNode('controllers_by_class')
                            ->useAttributeAsKey('alias')
                            ->prototype('scalar')
                        ->end()->end()
                        ->arrayNode('templates_by_class')
                            ->useAttributeAsKey('alias')
                            ->prototype('scalar')
                        ->end()->end()
                        ->scalarNode('route_entity_class')->end()
                    ->end()
                ->end()
        ->end();

        return $treeBuilder;
    }
}
