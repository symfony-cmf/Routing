<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Routing;

use Symfony\Cmf\Component\Routing\LazyRouteCollection;
use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class LazyRouteCollectionTest extends CmfUnitTestCase {

    /**
     * Contains a mocked route provider.
     *
     * @var \Symfony\Cmf\Component\Routing\RangedRouteProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $routeProvider;

    protected function setUp()
    {
        $this->routeProvider = $this->getMock('Symfony\Cmf\Component\Routing\RangedRouteProviderInterface');
    }


    /**
     * Tests iterating a small amount of routes.
     */
    public function testIteratingSmall()
    {
        $routes = array();
        for ($i = 0; $i < 10; $i++) {
            $routes['test_' . $i] = $routes;
        }
        $names = array_keys($routes);
        $this->routeProvider->expects($this->exactly(2))
            ->method('getRoutesRanged')
            ->with(0, 50)
            ->will($this->returnValue($routes));

        $route_collection = new LazyRouteCollection($this->routeProvider);

        $counter = 0;
        foreach ($route_collection as $route_name => $route) {
            // Ensure the route did not changed.
            $this->assertEquals($routes[$route_name], $route);
            // Ensure that the order did not changed.
            $this->assertEquals($route_name, $names[$counter]);
            $counter++;
        }
    }
}

