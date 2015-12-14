<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\DependencyInjection\Compiler;

use Symfony\Cmf\Component\Routing\DependencyInjection\Compiler\RegisterRouteEnhancersPass;
use Symfony\Component\DependencyInjection\Definition;

class RegisterRouteEnhancersPassTest extends \PHPUnit_Framework_TestCase
{
    public function testRouteEnhancerPass()
    {
        $serviceIds = array(
            'test_enhancer' => array(
                0 => array(
                    'id' => 'foo_enhancer',
                ),
            ),
        );

        $builder = $this->getContainerBuilderMock();
        $definition = new Definition('router');
        $builder->expects($this->at(0))
            ->method('hasDefinition')
            ->with('cmf_routing.dynamic_router')
            ->will($this->returnValue(true))
        ;
        $builder->expects($this->any())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($serviceIds))
        ;
        $builder->expects($this->any())
            ->method('getDefinition')
            ->with('cmf_routing.dynamic_router')
            ->will($this->returnValue($definition))
        ;

        $pass = new RegisterRouteEnhancersPass();
        $pass->process($builder);

        $calls = $definition->getMethodCalls();
        $this->assertEquals(1, count($calls));
        $this->assertEquals('addRouteEnhancer', $calls[0][0]);
    }

    /**
     * If there is no dynamic router defined in the container builder, nothing
     * should be processed.
     */
    public function testNoDynamicRouter()
    {
        $builder = $this->getContainerBuilderMock();
        $builder->expects($this->once())
            ->method('hasDefinition')
            ->with('cmf_routing.dynamic_router')
            ->will($this->returnValue(false))
        ;

        $pass = new RegisterRouteEnhancersPass();
        $pass->process($builder);
    }

    protected function getContainerBuilderMock(array $functions = array())
    {
        return $this->getMock(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            array_merge(array('hasDefinition', 'findTaggedServiceIds', 'getDefinition'), $functions)
        );
    }
}
