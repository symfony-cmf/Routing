<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Routing;

use Symfony\Cmf\Component\Routing\Event\Events;
use Symfony\Cmf\Component\Routing\Event\RouterMatchEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Cmf\Component\Routing\DynamicRouter;
use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class DynamicRouterTest extends CmfUnitTestCase
{
    protected $routeDocument;
    protected $matcher;
    protected $generator;
    protected $enhancer;
    /** @var DynamicRouter */
    protected $router;
    protected $context;
    public $request;

    const URL = '/foo/bar';

    public function setUp()
    {
        $this->routeDocument = $this->buildMock('Symfony\Cmf\Component\Routing\Tests\Routing\RouteMock', array('getDefaults'));

        $this->matcher = $this->buildMock('Symfony\Component\Routing\Matcher\UrlMatcherInterface');
        $this->generator = $this->buildMock('Symfony\Cmf\Component\Routing\VersatileGeneratorInterface', array('supports', 'generate', 'setContext', 'getContext', 'getRouteDebugMessage'));
        $this->enhancer = $this->buildMock('Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface', array('enhance', 'isName'));

        $this->context = $this->buildMock('Symfony\Component\Routing\RequestContext');
        $this->request = Request::create(self::URL);

        $this->router = new DynamicRouter($this->context, $this->matcher, $this->generator, $this->enhancer);
    }

    /**
     * rather trivial, but we want 100% coverage.
     */
    public function testContext()
    {
        $this->router->setContext($this->context);
        $this->assertSame($this->context, $this->router->getContext());
    }

    public function testRouteCollectionEmpty()
    {
        $collection = $this->router->getRouteCollection();
        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $collection);
    }

    public function testRouteCollectionLazy()
    {
        $provider = $this->getMock('Symfony\Cmf\Component\Routing\RouteProviderInterface');
        $router = new DynamicRouter($this->context, $this->matcher, $this->generator, $this->enhancer, '', null, $provider);

        $collection = $router->getRouteCollection();
        $this->assertInstanceOf('Symfony\Cmf\Component\Routing\LazyRouteCollection', $collection);
    }

    /// generator tests ///

    public function testGetGenerator()
    {
        $this->generator->expects($this->once())
            ->method('setContext')
            ->with($this->equalTo($this->context));

        $generator = $this->router->getGenerator();
        $this->assertInstanceOf('Symfony\Component\Routing\Generator\UrlGeneratorInterface', $generator);
        $this->assertSame($this->generator, $generator);
    }

    public function testGenerate()
    {
        $name = 'my_route_name';
        $parameters = array('foo' => 'bar');
        $absolute = UrlGeneratorInterface::ABSOLUTE_PATH;

        $this->generator->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, $absolute)
            ->will($this->returnValue('http://test'))
        ;

        $url = $this->router->generate($name, $parameters, $absolute);
        $this->assertEquals('http://test', $url);
    }

    public function testSupports()
    {
        $name = 'foo/bar';
        $this->generator->expects($this->once())
            ->method('supports')
            ->with($this->equalTo($name))
            ->will($this->returnValue(true))
        ;

        $this->assertTrue($this->router->supports($name));
    }

    public function testSupportsNonversatile()
    {
        $generator = $this->buildMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface', array('generate', 'setContext', 'getContext'));
        $router = new DynamicRouter($this->context, $this->matcher, $generator, $this->enhancer);
        $this->assertInternalType('string', $router->getRouteDebugMessage('test'));

        $this->assertTrue($router->supports('some string'));
        $this->assertFalse($router->supports($this));
    }

    /// match tests ///

    public function testGetMatcher()
    {
        $this->matcher->expects($this->once())
            ->method('setContext')
            ->with($this->equalTo($this->context));

        $matcher = $this->router->getMatcher();
        $this->assertInstanceOf('Symfony\Component\Routing\Matcher\UrlMatcherInterface', $matcher);
        $this->assertSame($this->matcher, $matcher);
    }

    /**
     * @group legacy
     */
    public function testMatchUrl()
    {
        $routeDefaults = array('foo' => 'bar');
        $this->matcher->expects($this->once())
            ->method('match')
            ->with(self::URL)
            ->will($this->returnValue($routeDefaults))
        ;

        $expected = array('this' => 'that');
        $test = $this;
        $this->enhancer->expects($this->once())
            ->method('enhance')
            ->with($this->equalTo($routeDefaults), $this->callback(function (Request $request) use ($test) {
                return DynamicRouterTest::URL === $request->server->get('REQUEST_URI');
            }))
            ->will($this->returnValue($expected))
        ;

        $results = $this->router->match(self::URL);

        $this->assertEquals($expected, $results);
    }

    public function testMatchRequestWithUrlMatcher()
    {
        $routeDefaults = array('foo' => 'bar');

        $this->matcher->expects($this->once())
            ->method('match')
            ->with(self::URL)
            ->will($this->returnValue($routeDefaults))
        ;

        $expected = array('this' => 'that');
        $test = $this;
        $this->enhancer->expects($this->once())
            ->method('enhance')
            ->with($this->equalTo($routeDefaults), $this->callback(function (Request $request) use ($test) {
                return DynamicRouterTest::URL === $request->server->get('REQUEST_URI');
            }))
            ->will($this->returnValue($expected))
        ;

        $results = $this->router->matchRequest($this->request);

        $this->assertEquals($expected, $results);
    }

    public function testMatchRequest()
    {
        $routeDefaults = array('foo' => 'bar');

        $matcher = $this->buildMock('Symfony\Component\Routing\Matcher\RequestMatcherInterface', array('matchRequest', 'setContext', 'getContext'));
        $router = new DynamicRouter($this->context, $matcher, $this->generator, $this->enhancer);

        $matcher->expects($this->once())
            ->method('matchRequest')
            ->with($this->request)
            ->will($this->returnValue($routeDefaults))
        ;

        $expected = array('this' => 'that');
        $test = $this;
        $this->enhancer->expects($this->once())
            ->method('enhance')
            ->with($this->equalTo($routeDefaults), $this->callback(function (Request $request) use ($test) {
                return DynamicRouterTest::URL === $request->server->get('REQUEST_URI');
            }))
            ->will($this->returnValue($expected))
        ;

        $this->assertEquals($expected, $router->matchRequest($this->request));
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @group legacy
     */
    public function testMatchFilter()
    {
        $router = new DynamicRouter($this->context, $this->matcher, $this->generator, $this->enhancer, '#/different/prefix.*#');

        $this->matcher->expects($this->never())
            ->method('match')
        ;

        $this->enhancer->expects($this->never())
            ->method('enhance')
        ;

        $router->match(self::URL);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatchRequestFilter()
    {
        $matcher = $this->buildMock('Symfony\Component\Routing\Matcher\RequestMatcherInterface', array('matchRequest', 'setContext', 'getContext'));

        $router = new DynamicRouter($this->context, $matcher, $this->generator, $this->enhancer, '#/different/prefix.*#');

        $matcher->expects($this->never())
            ->method('matchRequest')
        ;

        $this->enhancer->expects($this->never())
            ->method('enhance')
        ;

        $router->matchRequest($this->request);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @group legacy
     */
    public function testMatchUrlWithRequestMatcher()
    {
        $matcher = $this->buildMock('Symfony\Component\Routing\Matcher\RequestMatcherInterface', array('matchRequest', 'setContext', 'getContext'));
        $router = new DynamicRouter($this->context, $matcher, $this->generator, $this->enhancer);

        $router->match(self::URL);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidMatcher()
    {
        new DynamicRouter($this->context, $this, $this->generator, $this->enhancer);
    }

    public function testRouteDebugMessage()
    {
        $this->generator->expects($this->once())
            ->method('getRouteDebugMessage')
            ->with($this->equalTo('test'), $this->equalTo(array()))
            ->will($this->returnValue('debug message'))
        ;

        $this->assertEquals('debug message', $this->router->getRouteDebugMessage('test'));
    }

    public function testRouteDebugMessageNonversatile()
    {
        $generator = $this->buildMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface', array('generate', 'setContext', 'getContext'));
        $router = new DynamicRouter($this->context, $this->matcher, $generator, $this->enhancer);
        $this->assertInternalType('string', $router->getRouteDebugMessage('test'));
    }

    /**
     * @group legacy
     */
    public function testEventHandler()
    {
        $eventDispatcher = $this->buildMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $router = new DynamicRouter($this->context, $this->matcher, $this->generator, $this->enhancer, '', $eventDispatcher);

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(Events::PRE_DYNAMIC_MATCH, $this->equalTo(new RouterMatchEvent()))
        ;

        $routeDefaults = array('foo' => 'bar');
        $this->matcher->expects($this->once())
            ->method('match')
            ->with(self::URL)
            ->will($this->returnValue($routeDefaults))
        ;
        $this->enhancer->expects($this->once())
            ->method('enhance')
            ->with($this->equalTo($routeDefaults))
            ->will($this->returnValue($routeDefaults));

        $this->assertEquals($routeDefaults, $router->match(self::URL));
    }

    public function testEventHandlerRequest()
    {
        $eventDispatcher = $this->buildMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $router = new DynamicRouter($this->context, $this->matcher, $this->generator, $this->enhancer, '', $eventDispatcher);

        $that = $this;
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(Events::PRE_DYNAMIC_MATCH_REQUEST, $this->callback(function ($event) use ($that) {
                $that->assertInstanceOf('Symfony\Cmf\Component\Routing\Event\RouterMatchEvent', $event);
                $that->assertEquals($that->request, $event->getRequest());

                return true;
            }))
        ;

        $routeDefaults = array('foo' => 'bar');
        $this->matcher->expects($this->once())
            ->method('match')
            ->with(self::URL)
            ->will($this->returnValue($routeDefaults))
        ;
        $this->enhancer->expects($this->once())
            ->method('enhance')
            ->with($this->equalTo($routeDefaults))
            ->will($this->returnValue($routeDefaults));

        $this->assertEquals($routeDefaults, $router->matchRequest($this->request));
    }

    public function testEventHandlerGenerate()
    {
        $eventDispatcher = $this->buildMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $router = new DynamicRouter($this->context, $this->matcher, $this->generator, $this->enhancer, '', $eventDispatcher);

        $oldname = 'old_route_name';
        $newname = 'new_route_name';
        $oldparameters = array('foo' => 'bar');
        $newparameters = array('a' => 'b');
        $oldReferenceType = false;
        $newReferenceType = true;

        $that = $this;
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(Events::PRE_DYNAMIC_GENERATE, $this->callback(function ($event) use ($that, $oldname, $newname, $oldparameters, $newparameters, $oldReferenceType, $newReferenceType) {
                $that->assertInstanceOf('Symfony\Cmf\Component\Routing\Event\RouterGenerateEvent', $event);
                if (empty($that->seen)) {
                    // phpunit is calling the callback twice, and because we update the event the second time fails
                    $that->seen = true;
                } else {
                    return true;
                }
                $that->assertEquals($oldname, $event->getRoute());
                $that->assertEquals($oldparameters, $event->getParameters());
                $that->assertEquals($oldReferenceType, $event->getReferenceType());
                $event->setRoute($newname);
                $event->setParameters($newparameters);
                $event->setReferenceType($newReferenceType);

                return true;
            }))
        ;

        $this->generator->expects($this->once())
            ->method('generate')
            ->with($newname, $newparameters, $newReferenceType)
            ->will($this->returnValue('http://test'))
        ;

        $this->assertEquals('http://test', $router->generate($oldname, $oldparameters, $oldReferenceType));
    }
}
