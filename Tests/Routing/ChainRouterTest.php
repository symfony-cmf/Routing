<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Routing;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\ChainRouter;

class ChainRouterTest extends \PHPUnit_Framework_TestCase
{
    public function testPriority()
    {
        $router = new ChainRouter();
        $this->assertEquals(array(), $router->all());

        $low = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $high = $this->getMock('Symfony\Component\Routing\RouterInterface');

        $router->add($low, 10);
        $router->add($high, 100);

        $this->assertEquals(array(
            $high,
            $low,
        ), $router->all());
    }

    public function testContext()
    {
        $router = new ChainRouter();
        $context = $this->getMock('Symfony\Component\Routing\RequestContext');

        $low = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $low
            ->expects($this->once())
            ->method('setContext')
            ->with($context)
        ;

        $high = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $high
            ->expects($this->once())
            ->method('setContext')
            ->with($context)
        ;


        $router->add($low, 10);
        $router->add($high, 100);

        $router->setContext($context);
    }
}
