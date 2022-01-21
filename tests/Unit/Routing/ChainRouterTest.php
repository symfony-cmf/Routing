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
use Psr\Log\LoggerInterface;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class ChainRouterTest extends TestCase
{
    private ChainRouter $router;

    /**
     * @var RequestContext&MockObject
     */
    private $context;

    public function setUp(): void
    {
        $this->router = new ChainRouter($this->createMock(LoggerInterface::class));
        $this->context = $this->createMock(RequestContext::class);
    }

    public function testPriority(): void
    {
        $this->assertEquals([], $this->router->all());

        [$low, $high] = $this->createRouterMocks();

        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->assertEquals([
            $high,
            $low,
        ], $this->router->all());
    }

    public function testHasRouters(): void
    {
        $this->assertEquals([], $this->router->all());
        $this->assertFalse($this->router->hasRouters());

        [$low, $high] = $this->createRouterMocks();

        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->assertTrue($this->router->hasRouters());
    }

    /**
     * Routers are supposed to be sorted only once.
     * This test will check that by trying to get all routers several times.
     *
     * @covers \Symfony\Cmf\Component\Routing\ChainRouter::sortRouters
     * @covers \Symfony\Cmf\Component\Routing\ChainRouter::all
     */
    public function testSortRouters(): void
    {
        [$low, $medium, $high] = $this->createRouterMocks();
        // We're using a mock here and not $this->router because we need to ensure that the sorting operation is done only once.
        /** @var $router ChainRouter&MockObject */
        $router = $this->getMockBuilder(ChainRouter::class)
            ->disableOriginalConstructor()
            ->setMethods(['sortRouters'])
            ->getMock();
        $router
            ->expects($this->once())
            ->method('sortRouters')
            ->willReturn(
                [$high, $medium, $low]
            )
        ;

        $router->add($low, 10);
        $router->add($medium, 50);
        $router->add($high, 100);
        $expectedSortedRouters = [$high, $medium, $low];
        // Let's get all routers 5 times, we should only sort once.
        for ($i = 0; $i < 5; ++$i) {
            $this->assertSame($expectedSortedRouters, $router->all());
        }
    }

    /**
     * This test ensures that if a router is being added on the fly, the sorting is reset.
     *
     * @covers \Symfony\Cmf\Component\Routing\ChainRouter::sortRouters
     * @covers \Symfony\Cmf\Component\Routing\ChainRouter::all
     * @covers \Symfony\Cmf\Component\Routing\ChainRouter::add
     */
    public function testReSortRouters(): void
    {
        [$low, $medium, $high] = $this->createRouterMocks();
        $highest = clone $high;
        // We're using a mock here and not $this->router because we need to ensure that the sorting operation is done only once.
        /** @var $router ChainRouter&MockObject */
        $router = $this->getMockBuilder(ChainRouter::class)
            ->disableOriginalConstructor()
            ->setMethods(['sortRouters'])
            ->getMock();
        $router
            ->expects($this->exactly(2))
            ->method('sortRouters')
            ->willReturnOnConsecutiveCalls(
                [$high, $medium, $low],
                // The second time sortRouters() is called, we're supposed to get the newly added router ($highest)
                [$highest, $high, $medium, $low]
            )
        ;

        $router->add($low, 10);
        $router->add($medium, 50);
        $router->add($high, 100);
        $this->assertSame([$high, $medium, $low], $router->all());

        // Now adding another router on the fly, sorting must have been reset
        $router->add($highest, 101);
        $this->assertSame([$highest, $high, $medium, $low], $router->all());
    }

    /**
     * context must be propagated to chained routers and be stored locally.
     */
    public function testContext(): void
    {
        [$low, $high] = $this->createRouterMocks();

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
     * context must be propagated also when routers are added after context is set.
     */
    public function testContextOrder(): void
    {
        [$low, $high] = $this->createRouterMocks();

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

        $this->router->setContext($this->context);

        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->router->all();

        $this->assertSame($this->context, $this->router->getContext());
    }

    /**
     * The first usable match is used, no further routers are queried once a match is found.
     */
    public function testMatch(): void
    {
        $url = '/test';
        [$lower, $low, $high] = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->willReturn(['test'])
        ;
        $lower
            ->expects($this->never())
            ->method('match');
        $this->router->add($lower, 5);
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $result = $this->router->match('/test');
        $this->assertEquals(['test'], $result);
    }

    /**
     * The first usable match is used, no further routers are queried once a match is found.
     */
    public function testMatchRequest(): void
    {
        $url = '/test';
        [$lower, $low, $high] = $this->createRouterMocks();

        $highest = $this->createMock(RequestMatcher::class);

        $request = Request::create('/test');

        $highest
            ->expects($this->once())
            ->method('matchRequest')
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->willReturn(['test'])
        ;
        $lower
            ->expects($this->never())
            ->method('match')
        ;

        $this->router->add($lower, 5);
        $this->router->add($low, 10);
        $this->router->add($high, 100);
        $this->router->add($highest, 200);

        $result = $this->router->matchRequest($request);
        $this->assertEquals(['test'], $result);
    }

    /**
     * Call match on ChainRouter that has RequestMatcher in the chain.
     */
    public function testMatchWithRequestMatchers(): void
    {
        $url = '/test';

        [$low] = $this->createRouterMocks();

        $high = $this->createMock(RequestMatcher::class);

        $high
            ->expects($this->once())
            ->method('matchRequest')
            ->with($this->callback(function (Request $r) use ($url) {
                return $r->getPathInfo() === $url;
            }))
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->willReturn(['test'])
        ;

        $this->router->add($low, 10);
        $this->router->add($high, 20);

        $result = $this->router->match($url);
        $this->assertEquals(['test'], $result);
    }

    public function provideBaseUrl(): array
    {
        return [
            [''],
            ['/web'],
        ];
    }

    /**
     * Call match on ChainRouter that has RequestMatcher in the chain.
     *
     * @dataProvider provideBaseUrl
     */
    public function testMatchWithRequestMatchersAndContext(string $baseUrl): void
    {
        $url = '//test';

        [$low] = $this->createRouterMocks();

        $high = $this->createMock(RequestMatcher::class);

        $high
            ->expects($this->once())
            ->method('matchRequest')
            ->with($this->callback(function (Request $r) use ($url, $baseUrl) {
                return true === $r->isSecure()
                    && 'foobar.com' === $r->getHost()
                    && 4433 === $r->getPort()
                    && $baseUrl === $r->getBaseUrl()
                    && $url === $r->getPathInfo()
                ;
            }))
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->willReturn(['test'])
        ;

        $this->router->add($low, 10);
        $this->router->add($high, 20);

        $requestContext = new RequestContext();
        $requestContext->setScheme('https');
        $requestContext->setHost('foobar.com');
        $requestContext->setHttpsPort(4433);
        $requestContext->setBaseUrl($baseUrl);
        $this->router->setContext($requestContext);

        $result = $this->router->match($url);
        $this->assertEquals(['test'], $result);
    }

    /**
     * If there is a method not allowed but another router matches, that one is used.
     */
    public function testMatchAndNotAllowed(): void
    {
        $url = '/test';
        [$low, $high] = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new MethodNotAllowedException([])))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->willReturn(['test'])
        ;
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $result = $this->router->match('/test');
        $this->assertEquals(['test'], $result);
    }

    /**
     * If there is a method not allowed but another router matches, that one is used.
     */
    public function testMatchRequestAndNotAllowed(): void
    {
        $url = '/test';
        [$low, $high] = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new MethodNotAllowedException([])))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->willReturn(['test'])
        ;
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $result = $this->router->matchRequest(Request::create('/test'));
        $this->assertEquals(['test'], $result);
    }

    public function testMatchNotFound(): void
    {
        $url = '/test';
        [$low, $high] = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->expectException(ResourceNotFoundException::class);
        $this->router->match('/test');
    }

    public function testMatchRequestNotFound(): void
    {
        $url = '/test';
        [$low, $high] = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->expectException(ResourceNotFoundException::class);
        $this->router->matchRequest(Request::create('/test'));
    }

    /**
     * Call match on ChainRouter that has RequestMatcher in the chain.
     */
    public function testMatchWithRequestMatchersNotFound(): void
    {
        $url = '/test';
        $expected = Request::create('/test');
        $expected->server->remove('REQUEST_TIME_FLOAT');

        $high = $this->createMock(RequestMatcher::class);

        $high
            ->expects($this->once())
            ->method('matchRequest')
            ->with($this->callback(function (Request $actual) use ($expected): bool {
                $actual->server->remove('REQUEST_TIME_FLOAT');

                return $actual == $expected;
            }))
            ->will($this->throwException(new ResourceNotFoundException()))
        ;

        $this->router->add($high, 20);

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('None of the routers in the chain matched url \'/test\'');
        $this->router->match($url);
    }

    /**
     * If any of the routers throws a not allowed exception and no other matches, we need to see this.
     */
    public function testMatchMethodNotAllowed(): void
    {
        $url = '/test';
        [$low, $high] = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new MethodNotAllowedException([])))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->expectException(MethodNotAllowedException::class);
        $this->router->match('/test');
    }

    /**
     * If any of the routers throws a not allowed exception and no other matches, we need to see this.
     */
    public function testMatchRequestMethodNotAllowed(): void
    {
        $url = '/test';
        [$low, $high] = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new MethodNotAllowedException([])))
        ;
        $low
            ->expects($this->once())
            ->method('match')
            ->with($url)
            ->will($this->throwException(new ResourceNotFoundException()))
        ;
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->expectException(MethodNotAllowedException::class);
        $this->router->matchRequest(Request::create('/test'));
    }

    public function testGenerate(): void
    {
        $url = '/test';
        $name = 'test';
        $parameters = ['test' => 'value'];
        [$lower, $low, $high] = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH)
            ->will($this->throwException(new RouteNotFoundException()))
        ;
        $low
            ->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn($url)
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

    public function testGenerateNotFound(): void
    {
        $name = 'test';
        $parameters = ['test' => 'value'];
        [$low, $high] = $this->createRouterMocks();

        $high
            ->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH)
            ->will($this->throwException(new RouteNotFoundException()))
        ;
        $low->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH)
            ->will($this->throwException(new RouteNotFoundException()))
        ;
        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->expectException(RouteNotFoundException::class);
        $this->router->generate($name, $parameters);
    }

    /**
     * This test currently triggers a deprecation notice because of ChainRouter BC.
     */
    public function testGenerateWithObjectNameInParametersNotFoundVersatile(): void
    {
        $name = RouteObjectInterface::OBJECT_BASED_ROUTE_NAME;
        $parameters = ['test' => 'value', '_route_object' => new \stdClass()];

        $chainedRouter = $this->createMock(VersatileRouter::class);
        $chainedRouter->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH)
            ->will($this->throwException(new RouteNotFoundException()))
        ;
        $chainedRouter->expects($this->once())
            ->method('getRouteDebugMessage')
            ->with($name, $parameters)
            ->willReturn('message')
        ;

        $this->router->add($chainedRouter, 10);

        $this->expectException(RouteNotFoundException::class);
        $this->router->generate($name, $parameters);
    }

    public function testGenerateWithObjectNameInParameters(): void
    {
        $name = RouteObjectInterface::OBJECT_BASED_ROUTE_NAME;
        $parameters = ['test' => 'value', '_route_object' => new \stdClass()];

        $defaultRouter = $this->createMock(RouterInterface::class);

        $defaultRouter
            ->expects($this->once())
            ->method('generate')
            ->with($name, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/foo/bar')
        ;

        $this->router->add($defaultRouter, 200);

        $result = $this->router->generate($name, $parameters);
        $this->assertEquals('/foo/bar', $result);
    }

    public function testWarmup(): void
    {
        $dir = 'test_dir';
        [$low] = $this->createRouterMocks();

        $high = $this->createMock(WarmableRouterMock::class);
        $high
            ->expects($this->once())
            ->method('warmUp')
            ->with($dir)
        ;

        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $this->router->warmUp($dir);
    }

    public function testRouteCollection(): void
    {
        list($low, $high) = $this->createRouterMocks();
        $lowcol = new RouteCollection();
        $lowcol->add('low', $this->createMock(Route::class));
        $highcol = new RouteCollection();
        $highcol->add('high', $this->createMock(Route::class));

        $low
            ->expects($this->once())
            ->method('getRouteCollection')
            ->willReturn($lowcol)
        ;
        $high
            ->expects($this->once())
            ->method('getRouteCollection')
            ->willReturn($highcol)
        ;

        $this->router->add($low, 10);
        $this->router->add($high, 100);

        $collection = $this->router->getRouteCollection();

        $names = [];
        foreach ($collection->all() as $name => $route) {
            $this->assertInstanceOf(Route::class, $route);
            $names[] = $name;
        }
        $this->assertEquals(['high', 'low'], $names);
    }

    /**
     * @return array<RouterInterface&MockObject>
     */
    protected function createRouterMocks(): array
    {
        return [
            $this->createMock(RouterInterface::class),
            $this->createMock(RouterInterface::class),
            $this->createMock(RouterInterface::class),
        ];
    }
}

abstract class WarmableRouterMock implements RouterInterface, WarmableInterface
{
}

abstract class RequestMatcher implements RouterInterface, RequestMatcherInterface
{
}

abstract class VersatileRouter implements VersatileGeneratorInterface, RequestMatcherInterface
{
}
