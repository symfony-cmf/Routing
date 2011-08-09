<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Routing;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\ChainRouter;

class ChainRouterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->router = new ChainRouter();
        $this->context = $this->getMock('Symfony\Component\Routing\RequestContext');
    }

    public function testPriority()
    {
        $this->assertEquals(array(), $this->router->all());

        list($low, $high) = $this->createRouterMocks();

        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->assertEquals(array(
            $high,
            $low,
        ), $this->router->all());
    }

    public function testContext()
    {
        list($low, $high) = $this->createRouterMocks();

        $low
            ->expects($this->once())
            ->method('setContext')
            ->with($this->context)
        ;

        $high
            ->expects($this->once())
            ->method('setContext')
            ->with($this->context)
        ;


        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->router->setContext($this->context);
    }

    protected function createRouterMocks()
    {
        return array(
            $this->getMock('Symfony\Component\Routing\RouterInterface'),
            $this->getMock('Symfony\Component\Routing\RouterInterface'),
        );
    }
}
