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
use Symfony\Cmf\Component\Routing\ProviderBasedGenerator;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;

class ProviderBasedGeneratorTest extends TestCase
{
    /**
     * @var Route|MockObject
     */
    private $routeDocument;

    /**
     * @var CompiledRoute|MockObject
     */
    private $routeCompiled;

    /**
     * @var RouteProviderInterface|MockObject
     */
    private $provider;

    /**
     * @var ProviderBasedGenerator
     */
    private $generator;

    /**
     * @var RequestContext|MockObject
     */
    private $context;

    public function setUp()
    {
        $this->routeDocument = $this->createMock(Route::class);
        $this->routeCompiled = $this->createMock(CompiledRoute::class);
        $this->provider = $this->createMock(RouteProviderInterface::class);
        $this->context = $this->createMock(RequestContext::class);

        $this->generator = new TestableProviderBasedGenerator($this->provider);
    }

    public function testGenerateFromName()
    {
        $name = 'foo/bar';

        $this->provider->expects($this->once())
            ->method('getRouteByName')
            ->with($name)
            ->will($this->returnValue($this->routeDocument))
        ;
        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate($name));
    }

    public function testGenerateNotFound()
    {
        $name = 'foo/bar';

        $this->provider->expects($this->once())
            ->method('getRouteByName')
            ->with($name)
            ->will($this->returnValue(null))
        ;

        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate($name);
    }

    public function testGenerateFromRoute()
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;
        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate($this->routeDocument));
    }

    public function testSupports()
    {
        $this->assertTrue($this->generator->supports('foo/bar'));
        $this->assertTrue($this->generator->supports($this->routeDocument));
        $this->assertFalse($this->generator->supports($this));
    }

    public function testGetRouteDebugMessage()
    {
        $this->assertContains('/some/key', $this->generator->getRouteDebugMessage(new RouteObject()));
        $this->assertContains('/de/test', $this->generator->getRouteDebugMessage(new Route('/de/test')));
        $this->assertContains('/some/route', $this->generator->getRouteDebugMessage('/some/route'));
        $this->assertContains('a:1:{s:10:"route_name";s:7:"example";}', $this->generator->getRouteDebugMessage(['route_name' => 'example']));
    }

    /**
     * Tests the generate method with passing in a route object into generate().
     */
    public function testGenerateByRoute()
    {
        $this->generator = new ProviderBasedGenerator($this->provider);

        // Setup a route with a numeric parameter, but pass in a string, so it
        // fails and getRouteDebugMessage should be triggered.
        $route = new Route('/test');
        $route->setPath('/test/{number}');
        $route->setRequirement('number', '\+d');

        $this->generator->setStrictRequirements(true);

        $context = new RequestContext();
        $this->generator->setContext($context);

        $this->expectException(InvalidParameterException::class);
        $this->generator->generate($route, ['number' => 'string']);
    }
}

/**
 * Overwrite doGenerate to reduce amount of mocking needed.
 */
class TestableProviderBasedGenerator extends ProviderBasedGenerator
{
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = [])
    {
        return 'result_url';
    }
}

class RouteObject implements RouteObjectInterface
{
    public function getRouteKey()
    {
        return '/some/key';
    }

    public function getContent()
    {
        return;
    }
}
