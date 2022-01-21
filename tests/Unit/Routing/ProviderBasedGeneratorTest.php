<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Unit\Routing;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Component\Routing\ProviderBasedGenerator;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Route as SymfonyRoute;

class ProviderBasedGeneratorTest extends TestCase
{
    /**
     * @var Route&MockObject
     */
    private $routeDocument;

    /**
     * @var CompiledRoute
     */
    private $routeCompiled;

    /**
     * @var RouteProviderInterface&MockObject
     */
    private $provider;

    /**
     * @var ProviderBasedGenerator
     */
    private $generator;

    public function setUp(): void
    {
        $this->routeDocument = $this->createMock(Route::class);
        $this->routeCompiled = new CompiledRoute('', '', [], []);
        $this->provider = $this->createMock(RouteProviderInterface::class);

        $this->generator = new TestableProviderBasedGenerator($this->provider);
    }

    public function testGenerateFromName(): void
    {
        $name = 'foo/bar';

        $this->provider->expects($this->once())
            ->method('getRouteByName')
            ->with($name)
            ->willReturn($this->routeDocument)
        ;
        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;

        $this->assertEquals('result_url', $this->generator->generate($name));
    }

    public function testGenerateFromRoute(): void
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;
        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;

        $url = $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
            RouteObjectInterface::ROUTE_OBJECT => $this->routeDocument,
        ]);
        $this->assertEquals('result_url', $url);
    }

    public function testRemoveRouteObject(): void
    {
        $url = $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
            RouteObjectInterface::ROUTE_OBJECT => new Proxy('/path'),
        ]);

        $this->assertEquals('result_url', $url);
    }

    public function testGetRouteDebugMessage(): void
    {
        $this->assertStringContainsString('/some/route', $this->generator->getRouteDebugMessage('/some/route'));
    }

    /**
     * Tests the generate method with passing in a route object into generate().
     */
    public function testGenerateByRoute(): void
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
        $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
            RouteObjectInterface::ROUTE_OBJECT => $route,
            'number' => 'string',
        ]);
    }
}

/**
 * Overwrite doGenerate to reduce amount of mocking needed.
 */
class TestableProviderBasedGenerator extends ProviderBasedGenerator
{
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes = []): string
    {
        $url = 'result_url';
        if ($parameters && $query = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986)) {
            $url .= '?'.$query;
        }

        return $url;
    }
}

class RouteObject implements RouteObjectInterface
{
    public function getRouteKey(): string
    {
        return '/some/key';
    }

    public function getContent(): ?object
    {
        return null;
    }
}

class Proxy extends SymfonyRoute
{
    public $__isInitialized__ = true;
}
