<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * This compiler pass adds additional route enhancers
 * to the dynamic router.
 *
 * @author Daniel Leech <dan.t.leech@gmail.com>
 * @author Nathaniel Catchpole (catch)
 */
class RegisterRouteEnhancersPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    protected $conditionalEnhancer;

    protected $enhancerTag;

    public function __construct($conditionalEnhancer = 'cmf_routing.conditional_enhancer', $enhancerTag = 'dynamic_router_route_enhancer')
    {
        $this->conditionalEnhancer = $conditionalEnhancer;
        $this->enhancerTag = $enhancerTag;
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->conditionalEnhancer)) {
            return;
        }

        $router = $container->getDefinition($this->conditionalEnhancer);

        foreach ($container->findTaggedServiceIds($this->enhancerTag) as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $router->addMethodCall('addRouteEnhancer', array(new Reference($id), $priority));
        }
    }
}
