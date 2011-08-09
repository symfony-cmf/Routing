<?php

namespace Symfony\Bundle\ChainRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * ChainRouting configuration structure.
 *
 * @author Claudio Beatrice <claudio@agavee.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('chain_routing');

        // Add validation stuff here
        $rootNode
            ->children()
                ->arrayNode('subrouters')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->beforeNormalization()
                        ->ifTrue(function($v){ return !is_array($v); })
                        ->then(function($v){ return array($v); })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

}
