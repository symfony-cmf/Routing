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
 * Compiler pass to register routers to the ChainRouter.
 *
 * @author Wouter J <waldio.webdesign@gmail.com>
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 * @author Magnus Nordlander <magnus@e-butik.se>
 */
final class RegisterRoutersPass implements CompilerPassInterface
{
    private string $chainRouterServiceName;
    private string $routerTagName;

    public function __construct(
        string $chainRouterServiceName = 'cmf_routing.router',
        string $routerTagName = 'router'
    ) {
        $this->chainRouterServiceName = $chainRouterServiceName;
        $this->routerTagName = $routerTagName;
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->chainRouterServiceName)) {
            return;
        }

        $definition = $container->getDefinition($this->chainRouterServiceName);

        foreach ($container->findTaggedServiceIds($this->routerTagName) as $id => $attributes) {
            $priority = $attributes[0]['priority'] ?? 0;

            $definition->addMethodCall('add', [new Reference($id), $priority]);
        }
    }
}
