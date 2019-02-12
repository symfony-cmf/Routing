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
use Symfony\Cmf\Component\Routing\NestedMatcher\UrlMatcher;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\Tests\Unit\Routing\RouteMock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class UrlMatcherTest extends TestCase
{
    /**
     * @var RouteMock|MockObject
     */
    private $routeDocument;

    /**
     * @var CompiledRoute|MockObject
     */
    private $routeCompiled;

    /**
     * @var UrlMatcher
     */
    private $matcher;

    /**
     * @var RequestContext|MockObject
     */
    private $context;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $url = '/foo/bar';

    public function setUp()
    {
        $this->routeDocument = $this->createMock(RouteMock::class);
        $this->routeCompiled = $this->createMock(CompiledRoute::class);

        $this->context = $this->createMock(RequestContext::class);
        $this->request = Request::create($this->url);

        $this->matcher = new UrlMatcher(new RouteCollection(), $this->context);
    }

    public function testMatchRouteKey()
    {
        $this->doTestMatchRouteKey($this->url);
    }

    public function testMatchNoKey()
    {
        $this->doTestMatchRouteKey(null);
    }

    public function doTestMatchRouteKey($routeKey)
    {
        $this->routeCompiled->expects($this->atLeastOnce())
            ->method('getStaticPrefix')
            ->will($this->returnValue($this->url))
        ;
        $this->routeCompiled->expects($this->atLeastOnce())
            ->method('getRegex')
            ->will($this->returnValue('#'.str_replace('/', '\/', $this->url).'$#sD'))
        ;
        $this->routeDocument->expects($this->atLeastOnce())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;
        $this->routeDocument->expects($this->atLeastOnce())
            ->method('getRouteKey')
            ->will($this->returnValue($routeKey))
        ;
        $this->routeDocument->expects($this->atLeastOnce())
            ->method('getDefaults')
            ->will($this->returnValue(['foo' => 'bar']))
        ;

        $mockCompiled = $this->createMock(CompiledRoute::class);
        $mockCompiled->expects($this->any())
            ->method('getStaticPrefix')
            ->will($this->returnValue('/no/match'))
        ;
        $mockRoute = $this->createMock(Route::class);
        $mockRoute->expects($this->any())
            ->method('compile')
            ->will($this->returnValue($mockCompiled))
        ;
        $routeCollection = new RouteCollection();
        $routeCollection->add('some', $mockRoute);
        $routeCollection->add('_company_more', $this->routeDocument);
        $routeCollection->add('other', $mockRoute);

        $results = $this->matcher->finalMatch($routeCollection, $this->request);

        $expected = [
            RouteObjectInterface::ROUTE_NAME => $routeKey ? $routeKey : '_company_more',
            RouteObjectInterface::ROUTE_OBJECT => $this->routeDocument,
            'foo' => 'bar',
        ];

        $this->assertEquals($expected, $results);
    }

    public function testMatchNoRouteObject()
    {
        $this->routeCompiled->expects($this->atLeastOnce())
            ->method('getStaticPrefix')
            ->will($this->returnValue($this->url))
        ;
        $this->routeCompiled->expects($this->atLeastOnce())
            ->method('getRegex')
            ->will($this->returnValue('#'.str_replace('/', '\/', $this->url).'$#sD'))
        ;
        $this->routeDocument = $this->createMock(Route::class);
        $this->routeDocument->expects($this->atLeastOnce())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;
        $this->routeDocument->expects($this->atLeastOnce())
            ->method('getDefaults')
            ->will($this->returnValue(['foo' => 'bar']))
        ;

        $mockCompiled = $this->createMock(CompiledRoute::class);
        $mockCompiled->expects($this->any())
            ->method('getStaticPrefix')
            ->will($this->returnValue('/no/match'))
        ;
        $mockRoute = $this->createMock(Route::class);
        $mockRoute->expects($this->any())
            ->method('compile')
            ->will($this->returnValue($mockCompiled))
        ;
        $routeCollection = new RouteCollection();
        $routeCollection->add('some', $mockRoute);
        $routeCollection->add('_company_more', $this->routeDocument);
        $routeCollection->add('other', $mockRoute);

        $results = $this->matcher->finalMatch($routeCollection, $this->request);

        $expected = [
            RouteObjectInterface::ROUTE_NAME => '_company_more',
            RouteObjectInterface::ROUTE_OBJECT => $this->routeDocument,
            'foo' => 'bar',
        ];

        $this->assertEquals($expected, $results);
    }
}
