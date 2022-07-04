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

use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ChainRouteCollection extends RouteCollection
{
    /**
     * @var RouteCollection[]
     */
    private array $routeCollections = [];

    private RouteCollection $additionalRoutes;

    public function __clone()
    {
        foreach ($this->routeCollections as $routeCollection) {
            $this->routeCollections[] = clone $routeCollection;
        }
    }

    /**
     * Gets the number of Routes in this collection.
     */
    public function count(): int
    {
        $count = 0;
        foreach ($this->routeCollections as $routeCollection) {
            $count += $routeCollection->count();
        }

        return $count;
    }

    public function add(string $name, Route $route, int $priority = 0): void
    {
        $this->createInternalCollection();
        $this->additionalRoutes->add($name, $route, $priority);
    }

    /**
     * Returns all routes in this collection.
     *
     * @return array<string, Route> An array of routes
     */
    public function all(): array
    {
        $routeCollectionAll = new RouteCollection();
        foreach ($this->routeCollections as $routeCollection) {
            $routeCollectionAll->addCollection($routeCollection);
        }

        return $routeCollectionAll->all();
    }

    /**
     * Gets a route by name.
     */
    public function get(string $name): ?Route
    {
        foreach ($this->routeCollections as $routeCollection) {
            $route = $routeCollection->get($name);
            if (null !== $route) {
                return $route;
            }
        }

        return null;
    }

    public function remove(array|string $name): void
    {
        foreach ($this->routeCollections as $routeCollection) {
            $route = $routeCollection->get($name);
            if (null !== $route) {
                $routeCollection->remove($name);
            }
        }
    }

    /**
     * Adds a route collection at the end of the current set by appending all
     * routes of the added collection.
     */
    public function addCollection(RouteCollection $collection): void
    {
        $this->routeCollections[] = $collection;
    }

    public function addPrefix(string $prefix, array $defaults = [], array $requirements = []): void
    {
        $this->createInternalCollection();
        foreach ($this->routeCollections as $routeCollection) {
            $routeCollection->addPrefix($prefix, $defaults, $requirements);
        }
    }

    public function setHost(?string $pattern, array $defaults = [], array $requirements = []): void
    {
        $this->createInternalCollection();
        foreach ($this->routeCollections as $routeCollection) {
            $routeCollection->setHost($pattern, $defaults, $requirements);
        }
    }

    public function addDefaults(array $defaults): void
    {
        $this->createInternalCollection();
        foreach ($this->routeCollections as $routeCollection) {
            $routeCollection->addDefaults($defaults);
        }
    }

    public function addRequirements(array $requirements): void
    {
        $this->createInternalCollection();
        foreach ($this->routeCollections as $routeCollection) {
            $routeCollection->addRequirements($requirements);
        }
    }

    public function addOptions(array $options): void
    {
        $this->createInternalCollection();
        foreach ($this->routeCollections as $routeCollection) {
            $routeCollection->addOptions($options);
        }
    }

    public function setSchemes(array|string $schemes): void
    {
        $this->createInternalCollection();
        foreach ($this->routeCollections as $routeCollection) {
            $routeCollection->setSchemes($schemes);
        }
    }

    public function setMethods(array|string $methods): void
    {
        $this->createInternalCollection();
        foreach ($this->routeCollections as $routeCollection) {
            $routeCollection->setMethods($methods);
        }
    }

    /**
     * Returns an array of resources loaded to build this collection.
     *
     * @return ResourceInterface[] An array of resources
     */
    public function getResources(): array
    {
        if (0 === count($this->routeCollections)) {
            return [];
        }

        $resources = array_map(static function (RouteCollection $routeCollection) {
            return $routeCollection->getResources();
        }, $this->routeCollections);

        return array_unique(call_user_func_array('array_merge', $resources));
    }

    public function addResource(ResourceInterface $resource): void
    {
        $this->createInternalCollection();
        $this->additionalRoutes->addResource($resource);
    }

    private function createInternalCollection(): void
    {
        if (!isset($this->additionalRoutes)) {
            $this->additionalRoutes = new RouteCollection();
            $this->routeCollections[] = $this->additionalRoutes;
        }
    }
}
