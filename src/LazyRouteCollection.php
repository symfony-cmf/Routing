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

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class LazyRouteCollection extends RouteCollection
{
    private RouteProviderInterface $provider;

    public function __construct(RouteProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Gets the number of Routes in this collection.
     */
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * Returns all routes in this collection.
     *
     * @return array<string, Route> An array of routes
     */
    public function all(): array
    {
        $routes = $this->provider->getRoutesByNames(null);
        if (\is_array($routes)) {
            return $routes;
        }

        return \iterator_to_array($routes);
    }

    public function get(string $name): ?Route
    {
        try {
            return $this->provider->getRouteByName($name);
        } catch (RouteNotFoundException $e) {
            return null;
        }
    }
}
