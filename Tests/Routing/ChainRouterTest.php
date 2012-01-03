<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Routing;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\ChainRouter;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;

class ChainRouterTest extends CmfUnitTestCase
{
    public function setUp()
    {
        $this->router = new ChainRouter();
        $this->context = $this->getMock('Symfony\\Component\\Routing\\RequestContext');
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

    /**
     * context must be propagated to chained routers and be stored locally
     */
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
        $this->assertSame($this->context, $this->router->getContext());
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
        $high = $this->getMock('Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Tests\\Routing\\WarmableRouterMock');
        $high
            ->expects($this->once())
            ->method('warmUp')
            ->with($dir)
        ;

        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->router->warmUp($dir);
    }

    public function testRouteCollection()
    {
        list($low, $high) = $this->createRouterMocks();
        $lowcol = new \Symfony\Component\Routing\RouteCollection();
        $lowcol->add('low', $this->buildMock('Symfony\\Component\\Routing\\Route'));
        $highcol = new \Symfony\Component\Routing\RouteCollection();
        $highcol->add('high', $this->buildMock('Symfony\\Component\\Routing\\Route'));

        $low
            ->expects($this->once())
            ->method('getRouteCollection')
            ->will($this->returnValue($lowcol))
        ;
        $high
            ->expects($this->once())
            ->method('getRouteCollection')
            ->will($this->returnValue($highcol))
        ;

        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $collection = $this->router->getRouteCollection();
        $this->assertInstanceOf('Symfony\\Component\\Routing\\RouteCollection', $collection);

        $names = array();
        foreach ($collection->all() as $name => $route) {
            $this->assertInstanceOf('Symfony\\Component\\Routing\\Route', $route);
            $names[] = $name;
        }
        $this->assertEquals(array('high', 'low'), $names);
    }

    protected function createRouterMocks()
    {
        return array(
            $this->getMock('Symfony\\Component\\Routing\\RouterInterface'),
            $this->getMock('Symfony\\Component\\Routing\\RouterInterface'),
            $this->getMock('Symfony\\Component\\Routing\\RouterInterface'),
        );
    }
}

abstract class WarmableRouterMock implements \Symfony\Component\Routing\RouterInterface, \Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface
{
}