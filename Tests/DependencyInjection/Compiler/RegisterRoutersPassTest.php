<?php

namespace Symfony\Cmf\Routing\Tests\DependencyInjection\Compiler;

use Symfony\Cmf\Component\Routing\DependencyInjection\RegisterRoutersPass;

use Symfony\Component\DependencyInjection\Reference;

class RegisterRoutersPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getValidRoutersData
     */
    public function testValidRouters($name, $priority = null)
    {
        if (!method_exists($this, 'callback')) {
            $this->markTestSkipped('PHPUnit version too old for this test');
        }
        $services = array();
        $services[$name] = array(0 => array('priority' => $priority));

        $priority = $priority ?: 0;

        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $definition->expects($this->atLeastOnce())
            ->method('addMethodCall')
            ->with($this->equalTo('add'), $this->callback(function($arg) use ($name, $priority) {
                if (!$arg[0] instanceof Reference || $name !== $arg[0]->__toString()) {
                    return false;
                }

                if ($priority !== $arg[1]) {
                    return false;
                }

                return true;
            }));

        $builder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder', array('hasDefinition', 'findTaggedServiceIds', 'getDefinition'));
        $builder->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        $builder->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($services));

        $builder->expects($this->atLeastOnce())
            ->method('getDefinition')
            ->will($this->returnValue($definition));

        $registerRoutersPass = new RegisterRoutersPass();
        $registerRoutersPass->process($builder);
    }

    public function getValidRoutersData()
    {
        return array(
            array('my_router'),
            array('my_primary_router', 99),
            array('my_router', 0),
        );
    }
}
