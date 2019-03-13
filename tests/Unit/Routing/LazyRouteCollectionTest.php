<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;

/**
 * Tests the lazy route collection.
 *
 * @group cmf/routing
 */
class LazyRouteCollectionTest extends TestCase
{
    /**
     * Tests the iterator without a paged route provider.
     */
    public function testGetIterator()
    {
        $routeProvider = $this->createMock(RouteProviderInterface::class);
        $testRoutes = [
          'route_1' => new Route('/route-1'),
          'route_2"' => new Route('/route-2'),
        ];
        $routeProvider->expects($this->exactly(2))
            ->method('getRoutesByNames')
            ->with(null)
            ->will($this->returnValue($testRoutes));
        $lazyRouteCollection = new LazyRouteCollection($routeProvider);
        $this->assertEquals($testRoutes, iterator_to_array($lazyRouteCollection->getIterator()));
        $this->assertEquals($testRoutes, $lazyRouteCollection->all());
    }
}
