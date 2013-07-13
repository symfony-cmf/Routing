<?php

namespace Symfony\Cmf\Component\Routing\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Compiler pass to register routers to the ChainRouter.
 *
 * @author Wouter J <waldio.webdesign@gmail.com>
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 * @author Magnus Nordlander <magnus@e-butik.se>
 */
class RegisterRoutersPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    protected $chainRouterService;

    protected $routerTag;

    public function __construct($chainRouterService = 'cmf_routing.router', $routerTag = 'router')
    {
        $this->chainRouterService = $chainRouterService;
        $this->routerTag = $routerTag;
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->chainRouterService)) {
            return;
        }

        $definition = $container->getDefinition($this->chainRouterService);

        foreach ($container->findTaggedServiceIds($this->routerTag) as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;

            $definition->addMethodCall('add', array(new Reference($id), $priority));
        }
    }
}