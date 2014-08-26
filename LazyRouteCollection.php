<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class LazyRouteCollection extends RouteCollection
{
    /**
     * The route provider for this generator.
     *
     * @var RouteProviderInterface
     */
    protected $provider;

    /**
     * The actual paged route collection.
     *
     * @var PagedRouteCollection
     */
    protected $pagedRouteCollection;

    public function __construct(RouteProviderInterface $provider)
    {
        $this->provider = $provider;
        if ($this->provider instanceof PagedRouteProviderInterface) {
            $this->pagedRouteCollection = new PagedRouteCollection($this->provider);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        if ($this->pagedRouteCollection) {
            return $this->pagedRouteCollection;
        }
        else {
            return new \ArrayIterator($this->all());
        }
    }

    /**
     * Gets the number of Routes in this collection.
     *
     * @return int The number of routes
     */
    public function count()
    {
        if ($this->provider instanceof PagedRouteProviderInterface) {
            return $this->provider->getRoutesCount();
        }
        else {
            return count($this->all());
        }
    }

    /**
     * Returns all routes in this collection.
     *
     * @return Route[] An array of routes
     */
    public function all()
    {
        if ($this->pagedRouteCollection) {
            return $this->pagedRouteCollection;
        }
        else {
            return $this->provider->getRoutesByNames(null);
        }
    }

    /**
     * Gets a route by name.
     *
     * @param string $name The route name
     *
     * @return Route|null A Route instance or null when not found
     */
    public function get($name)
    {
        try {
            return $this->provider->getRouteByName($name);
        } catch (RouteNotFoundException $e) {
            return null;
        }
    }
}
