<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
     * @var string Service name of the dynamic router
     */
    private $dynamicRouterService;

    /**
     * @var string Name of the tag
     */
    private $enhancerTag;

    public function __construct($dynamicRouterService = 'cmf_routing.dynamic_router', $enhancerTag = 'dynamic_router_route_enhancer')
    {
        $this->dynamicRouterService = $dynamicRouterService;
        $this->enhancerTag = $enhancerTag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->dynamicRouterService)) {
            return;
        }

        $router = $container->getDefinition($this->dynamicRouterService);

        foreach ($container->findTaggedServiceIds($this->enhancerTag) as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $router->addMethodCall('addRouteEnhancer', [new Reference($id), $priority]);
        }
    }
}
