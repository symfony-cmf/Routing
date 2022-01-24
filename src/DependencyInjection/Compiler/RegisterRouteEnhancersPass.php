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
final class RegisterRouteEnhancersPass implements CompilerPassInterface
{
    private string $dynamicRouterServiceName;
    private string $enhancerTagName;

    public function __construct(
        string $dynamicRouterServiceName = 'cmf_routing.dynamic_router',
        string $enhancerTagName = 'dynamic_router_route_enhancer'
    ) {
        $this->dynamicRouterServiceName = $dynamicRouterServiceName;
        $this->enhancerTagName = $enhancerTagName;
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->dynamicRouterServiceName)) {
            return;
        }

        $router = $container->getDefinition($this->dynamicRouterServiceName);

        foreach ($container->findTaggedServiceIds($this->enhancerTagName) as $id => $attributes) {
            $priority = $attributes[0]['priority'] ?? 0;
            $router->addMethodCall('addRouteEnhancer', [new Reference($id), $priority]);
        }
    }
}
