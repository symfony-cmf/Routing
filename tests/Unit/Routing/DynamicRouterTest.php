<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Routing;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Component\Routing\DynamicRouter;
use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Cmf\Component\Routing\Event\Events;
use Symfony\Cmf\Component\Routing\Event\RouterGenerateEvent;
use Symfony\Cmf\Component\Routing\Event\RouterMatchEvent;
use Symfony\Cmf\Component\Routing\LazyRouteCollection;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Cmf\Component\Routing\Tests\Unit\Routing\RouteMock;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class DynamicRouterTest extends TestCase
{
    /**
     * @var RouteMock|MockObject
     */
    private $routeDocument;

    /**
     * @var UrlMatcherInterface|MockObject
     */
    private $matcher;

    /**
     * @var VersatileGeneratorInterface|MockObject
     */
    private $generator;

    /**
     * @var RouteEnhancerInterface|MockObject
     */
    private $enhancer;

    /**
     * @var DynamicRouter
     */
    private $router;

    /**
     * @var RequestContext|MockObject
     */
    private $context;

    /**
     * @var Request
     */
    private $request;

    const URL = '/foo/bar';

    public function setUp()
    {
        $this->routeDocument = $this->createMock(RouteMock::class);

        $this->matcher = $this->createMock(UrlMatcherInterface::class);
        $this->generator = $this->createMock(VersatileGeneratorInterface::class);
        $this->enhancer = $this->createMock(RouteEnhancerInterface::class);

        $this->context = $this->createMock(RequestContext::class);
        $this->request = Request::create(self::URL);

        $this->router = new DynamicRouter($this->context, $this->matcher, $this->generator);
        $this->router->addRouteEnhancer($this->enhancer);
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
        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function testRouteCollectionLazy()
    {
        $provider = $this->createMock(RouteProviderInterface::class);
        $router = new DynamicRouter($this->context, $this->matcher, $this->generator, '', null, $provider);

        $collection = $router->getRouteCollection();
        $this->assertInstanceOf(LazyRouteCollection::class, $collection);
    }

    /// generator tests ///

    public function testGetGenerator()
    {
        $this->generator->expects($this->once())
            ->method('setContext')
            ->with($this->equalTo($this->context));

        $generator = $this->router->getGenerator();
        $this->assertInstanceOf(UrlGeneratorInterface::class, $generator);
        $this->assertSame($this->generator, $generator);
    }

    public function testGenerate()
    {
        $name = 'my_route_name';
        $parameters = ['foo' => 'bar'];
        $absolute = UrlGeneratorInterface::ABSOLUTE_PATH;

        $this->generator->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, $absolute)
            ->will($this->returnValue('http://test'))
        ;

        $url = $this->router->generate($name, $parameters, $absolute);
        $this->assertEquals('http://test', $url);
    }

    /**
     * @group legacy
     */
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
        $generator = $this->createMock(UrlGeneratorInterface::class);
        $router = new DynamicRouter($this->context, $this->matcher, $generator);
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
        $this->assertInstanceOf(UrlMatcherInterface::class, $matcher);
        $this->assertSame($this->matcher, $matcher);
    }

    /**
     * @group legacy
     */
    public function testMatchUrl()
    {
        $routeDefaults = ['foo' => 'bar'];
        $this->matcher->expects($this->once())
            ->method('match')
            ->with(self::URL)
            ->will($this->returnValue($routeDefaults))
        ;

        $expected = ['this' => 'that'];
        $test = $this;
        $this->enhancer->expects($this->once())
            ->method('enhance')
            ->with($this->equalTo($routeDefaults), $this->callback(function (Request $request) use ($test) {
                return self::URL === $request->server->get('REQUEST_URI');
            }))
            ->will($this->returnValue($expected))
        ;

        $results = $this->router->match(self::URL);

        $this->assertEquals($expected, $results);
    }

    public function testMatchRequestWithUrlMatcher()
    {
        $routeDefaults = ['foo' => 'bar'];

        $this->matcher->expects($this->once())
            ->method('match')
            ->with(self::URL)
            ->will($this->returnValue($routeDefaults))
        ;

        $expected = ['this' => 'that'];
        $test = $this;
        $this->enhancer->expects($this->once())
            ->method('enhance')
            ->with($this->equalTo($routeDefaults), $this->callback(function (Request $request) use ($test) {
                return self::URL === $request->server->get('REQUEST_URI');
            }))
            ->will($this->returnValue($expected))
        ;

        $results = $this->router->matchRequest($this->request);

        $this->assertEquals($expected, $results);
    }

    public function testMatchRequest()
    {
        $routeDefaults = ['foo' => 'bar'];

        $matcher = $this->createMock(RequestMatcherInterface::class);
        $router = new DynamicRouter($this->context, $matcher, $this->generator);

        $matcher->expects($this->once())
            ->method('matchRequest')
            ->with($this->request)
            ->will($this->returnValue($routeDefaults))
        ;

        $expected = ['this' => 'that'];
        $test = $this;
        $this->enhancer->expects($this->once())
            ->method('enhance')
            ->with($this->equalTo($routeDefaults), $this->callback(function (Request $request) use ($test) {
                return self::URL === $request->server->get('REQUEST_URI');
            }))
            ->will($this->returnValue($expected))
        ;

        $router->addRouteEnhancer($this->enhancer);

        $this->assertEquals($expected, $router->matchRequest($this->request));
    }

    /**
     * @group legacy
     */
    public function testMatchFilter()
    {
        $router = new DynamicRouter($this->context, $this->matcher, $this->generator, '#/different/prefix.*#');
        $router->addRouteEnhancer($this->enhancer);

        $this->matcher->expects($this->never())
            ->method('match')
        ;

        $this->enhancer->expects($this->never())
            ->method('enhance')
        ;

        $this->expectException(ResourceNotFoundException::class);
        $router->match(self::URL);
    }

    public function testMatchRequestFilter()
    {
        $matcher = $this->createMock(RequestMatcherInterface::class);

        $router = new DynamicRouter($this->context, $matcher, $this->generator, '#/different/prefix.*#');
        $router->addRouteEnhancer($this->enhancer);

        $matcher->expects($this->never())
            ->method('matchRequest')
        ;

        $this->enhancer->expects($this->never())
            ->method('enhance')
        ;

        $this->expectException(ResourceNotFoundException::class);
        $router->matchRequest($this->request);
    }

    /**
     * @group legacy
     */
    public function testMatchUrlWithRequestMatcher()
    {
        $matcher = $this->createMock(RequestMatcherInterface::class);
        $router = new DynamicRouter($this->context, $matcher, $this->generator);

        $this->expectException(\InvalidArgumentException::class);
        $router->match(self::URL);
    }

    public function testInvalidMatcher()
    {
        $this->expectException(\InvalidArgumentException::class);
        new DynamicRouter($this->context, $this, $this->generator);
    }

    public function testRouteDebugMessage()
    {
        $this->generator->expects($this->once())
            ->method('getRouteDebugMessage')
            ->with($this->equalTo('test'), $this->equalTo([]))
            ->will($this->returnValue('debug message'))
        ;

        $this->assertEquals('debug message', $this->router->getRouteDebugMessage('test'));
    }

    public function testRouteDebugMessageNonversatile()
    {
        $generator = $this->createMock(UrlGeneratorInterface::class);
        $router = new DynamicRouter($this->context, $this->matcher, $generator);
        $this->assertInternalType('string', $router->getRouteDebugMessage('test'));
    }

    /**
     * @group legacy
     */
    public function testEventHandler()
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $router = new DynamicRouter($this->context, $this->matcher, $this->generator, '', $eventDispatcher);

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo(new RouterMatchEvent()), Events::PRE_DYNAMIC_MATCH)
        ;

        $routeDefaults = ['foo' => 'bar'];
        $this->matcher->expects($this->once())
            ->method('match')
            ->with(self::URL)
            ->will($this->returnValue($routeDefaults))
        ;

        $this->assertEquals($routeDefaults, $router->match(self::URL));
    }

    public function testEventHandlerRequest()
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $router = new DynamicRouter($this->context, $this->matcher, $this->generator, '', $eventDispatcher);

        $that = $this;
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($that) {
                $that->assertInstanceOf(RouterMatchEvent::class, $event);
                $that->assertEquals($that->request, $event->getRequest());

                return true;
            }), Events::PRE_DYNAMIC_MATCH_REQUEST)
        ;

        $routeDefaults = ['foo' => 'bar'];
        $this->matcher->expects($this->once())
            ->method('match')
            ->with(self::URL)
            ->will($this->returnValue($routeDefaults))
        ;

        $this->assertEquals($routeDefaults, $router->matchRequest($this->request));
    }

    public function testEventHandlerGenerate()
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $router = new DynamicRouter($this->context, $this->matcher, $this->generator, '', $eventDispatcher);

        $oldname = 'old_route_name';
        $newname = 'new_route_name';
        $oldparameters = ['foo' => 'bar'];
        $newparameters = ['a' => 'b'];
        $oldReferenceType = false;
        $newReferenceType = true;

        $that = $this;
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($that, $oldname, $newname, $oldparameters, $newparameters, $oldReferenceType, $newReferenceType) {
                $that->assertInstanceOf(RouterGenerateEvent::class, $event);
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
            }), Events::PRE_DYNAMIC_GENERATE)
        ;

        $this->generator->expects($this->once())
            ->method('generate')
            ->with($newname, $newparameters, $newReferenceType)
            ->will($this->returnValue('http://test'))
        ;

        $this->assertEquals('http://test', $router->generate($oldname, $oldparameters, $oldReferenceType));
    }
}
