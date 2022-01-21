<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Unit\NestedMatcher;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Component\Routing\NestedMatcher\FinalMatcherInterface;
use Symfony\Cmf\Component\Routing\NestedMatcher\NestedMatcher;
use Symfony\Cmf\Component\Routing\NestedMatcher\RouteFilterInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class NestedMatcherTest extends TestCase
{
    /**
     * @var MockObject&RouteProviderInterface
     */
    private $provider;

    /**
     * @var MockObject&RouteFilterInterface
     */
    private $routeFilter1;

    /**
     * @var MockObject&RouteFilterInterface
     */
    private $routeFilter2;

    /**
     * @var MockObject&RouteFilterInterface
     */
    private $finalMatcher;

    public function setUp(): void
    {
        $this->provider = $this->createMock(RouteProviderInterface::class);
        $this->routeFilter1 = $this->createMock(RouteFilterInterface::class);
        $this->routeFilter2 = $this->createMock(RouteFilterInterface::class);
        $this->finalMatcher = $this->createMock(FinalMatcherInterface::class);
    }

    public function testNestedMatcher(): void
    {
        $request = Request::create('/path/one');
        $routeCollection = new RouteCollection();
        $route = $this->createMock(Route::class);
        $routeCollection->add('route', $route);

        $this->provider->expects($this->once())
            ->method('getRouteCollectionForRequest')
            ->with($request)
            ->willReturn($routeCollection)
        ;
        $this->routeFilter1->expects($this->once())
            ->method('filter')
            ->with($routeCollection, $request)
            ->willReturn($routeCollection)
        ;
        $this->routeFilter2->expects($this->once())
            ->method('filter')
            ->with($routeCollection, $request)
            ->willReturn($routeCollection)
        ;
        $this->finalMatcher->expects($this->once())
            ->method('finalMatch')
            ->with($routeCollection, $request)
            ->willReturn(['foo' => 'bar'])
        ;

        $matcher = new NestedMatcher($this->provider, $this->finalMatcher);
        $matcher->addRouteFilter($this->routeFilter1);
        $matcher->addRouteFilter($this->routeFilter2);

        $attributes = $matcher->matchRequest($request);

        $this->assertEquals(['foo' => 'bar'], $attributes);
    }

    /**
     * Test priorities and exception handling.
     */
    public function testNestedMatcherPriority(): void
    {
        $request = Request::create('/path/one');
        $routeCollection = new RouteCollection();
        $route = $this->createMock(Route::class);
        $routeCollection->add('route', $route);

        $wrongProvider = $this->createMock(RouteProviderInterface::class);
        $wrongProvider->expects($this->never())
            ->method('getRouteCollectionForRequest')
        ;
        $this->provider->expects($this->once())
            ->method('getRouteCollectionForRequest')
            ->with($request)
            ->willReturn($routeCollection)
        ;
        $this->routeFilter1->expects($this->once())
            ->method('filter')
            ->with($routeCollection, $request)
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $this->routeFilter2->expects($this->never())
            ->method('filter')
        ;
        $this->finalMatcher->expects($this->never())
            ->method('finalMatch')
        ;

        $matcher = new NestedMatcher($wrongProvider, $this->finalMatcher);
        $matcher->setRouteProvider($this->provider);
        $matcher->addRouteFilter($this->routeFilter2, 10);
        $matcher->addRouteFilter($this->routeFilter1, 20);

        try {
            $matcher->matchRequest($request);
            $this->fail('nested matcher is eating exception');
        } catch (ResourceNotFoundException $e) {
            // expected
        }
    }

    public function testProviderNoMatch(): void
    {
        $request = Request::create('/path/one');
        $routeCollection = new RouteCollection();
        $this->provider->expects($this->once())
            ->method('getRouteCollectionForRequest')
            ->with($request)
            ->willReturn($routeCollection)
        ;
        $this->finalMatcher->expects($this->never())
            ->method('finalMatch')
        ;

        $matcher = new NestedMatcher($this->provider, $this->finalMatcher);

        $this->expectException(ResourceNotFoundException::class);
        $matcher->matchRequest($request);
    }
}
