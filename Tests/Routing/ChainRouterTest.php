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

    /**
     * The first usable match is used, no further routers are queried once a match is found
     */
    public function testMatch()
    {
        $url = '/test';
        list($lower, $low, $high) = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new \Symfony\Component\Routing\Exception\ResourceNotFoundException))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->returnValue(array('test')))
        ;
        $lower
            ->expects($this->never())
            ->method('match');
        $this->router->add($lower, 5);
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $result = $this->router->match('/test');
        $this->assertEquals(array('test'), $result);
    }

    /**
     * If there is a method not allowed but another router matches, that one is used
     */
    public function testMatchAndNotAllowed()
    {
        $url = '/test';
        list($low, $high) = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new \Symfony\Component\Routing\Exception\MethodNotAllowedException(array())))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->returnValue(array('test')))
        ;
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $result = $this->router->match('/test');
        $this->assertEquals(array('test'), $result);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatchNotFound()
    {
        $url = '/test';
        list($low, $high) = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new \Symfony\Component\Routing\Exception\ResourceNotFoundException))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new \Symfony\Component\Routing\Exception\ResourceNotFoundException))
        ;
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->router->match('/test');
    }

    /**
     * If any of the routers throws a not allowed exception and no other matches, we need to see this
     *
     * @expectedException \Symfony\Component\Routing\Exception\MethodNotAllowedException
     */
    public function testMatchMethodNotAllowed()
    {
        $url = '/test';
        list($low, $high) = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new \Symfony\Component\Routing\Exception\MethodNotAllowedException(array())))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new \Symfony\Component\Routing\Exception\ResourceNotFoundException))
        ;
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->router->match('/test');
    }

    public function testGenerate()
    {
        $url = '/test';
        $name = 'test';
        $parameters = array('test' => 'value');
        list($lower, $low, $high) = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, false)
            ->will($this->throwException(new \Symfony\Component\Routing\Exception\RouteNotFoundException()))
        ;
        $low
            ->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, false)
            ->will($this->returnValue($url))
        ;
        $lower
            ->expects($this->never())
            ->method('generate')
        ;

        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $result = $this->router->generate($name, $parameters);
        $this->assertEquals($url, $result);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateNotFound()
    {
        $url = '/test';
        $name = 'test';
        $parameters = array('test' => 'value');
        list($low, $high) = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, false)
            ->will($this->throwException(new \Symfony\Component\Routing\Exception\RouteNotFoundException()))
        ;
        $low->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, false)
            ->will($this->throwException(new \Symfony\Component\Routing\Exception\RouteNotFoundException()))
        ;
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $result = $this->router->generate($name, $parameters);
        $this->assertEquals($url, $result);
    }

    public function testWarmup()
    {
        $dir = 'test_dir';
        list($low) = $this->createRouterMocks();

        $low
            ->expects($this->never())
            ->method('warmUp')
        ;
        $high = $this->getMock('Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Routing\\WarmableRouterMock');
        $high
            ->expects($this->once())
            ->method('warmUp')
            ->with($dir)
        ;

        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->router->warmUp($dir);
    }

    protected function createRouterMocks()
    {
        return array(
            $this->getMock('Symfony\Component\Routing\RouterInterface'),
            $this->getMock('Symfony\Component\Routing\RouterInterface'),
            $this->getMock('Symfony\Component\Routing\RouterInterface'),
        );
    }
}

abstract class WarmableRouterMock implements \Symfony\Component\Routing\RouterInterface, \Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface
{
}