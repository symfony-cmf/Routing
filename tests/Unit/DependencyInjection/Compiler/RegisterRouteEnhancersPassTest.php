<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Unit\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Component\Routing\DependencyInjection\Compiler\RegisterRouteEnhancersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterRouteEnhancersPassTest extends TestCase
{
    public function testRouteEnhancerPass(): void
    {
        $serviceIds = [
            'test_enhancer' => [
                0 => [
                    'id' => 'foo_enhancer',
                ],
            ],
        ];

        $builder = $this->createMock(ContainerBuilder::class);
        $definition = new Definition('router');
        $builder->expects($this->atLeastOnce())
            ->method('hasDefinition')
            ->with('cmf_routing.dynamic_router')
            ->willReturn(true)
        ;
        $builder
            ->method('findTaggedServiceIds')
            ->willReturn($serviceIds)
        ;
        $builder
            ->method('getDefinition')
            ->with('cmf_routing.dynamic_router')
            ->willReturn($definition)
        ;

        $pass = new RegisterRouteEnhancersPass();
        $pass->process($builder);

        $calls = $definition->getMethodCalls();
        $this->assertCount(1, $calls);
        $this->assertEquals('addRouteEnhancer', $calls[0][0]);
    }

    /**
     * If there is no dynamic router defined in the container builder, nothing
     * should be processed.
     */
    public function testNoDynamicRouter(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $builder->expects($this->once())
            ->method('hasDefinition')
            ->with('cmf_routing.dynamic_router')
            ->willReturn(false)
        ;

        $pass = new RegisterRouteEnhancersPass();
        $pass->process($builder);
    }
}
