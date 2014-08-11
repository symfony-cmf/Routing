<?php


/**
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing;

/**
 * Provides a route collection which avoids having all routes in memory.
 *
 * Internally this does load multiple routes over time using a
 * PagedRouteProviderInterface $route_provider.
 */
class PagedRouteCollection implements \Iterator
{
    /**
     * The ranged route provider.
     *
     * @var PagedRouteProviderInterface
     */
    protected $provider;

    /**
     * Stores the amount of routes which are loaded in parallel and kept in
     * memory.
     *
     * @var int
     */
    protected $routesLoadedInParallel;

    /**
     * Contains the current item the iterator points to.
     *
     * @var int
     */
    protected $current = -1;

    /**
     * Stores the current loaded routes.
     *
     * @var \Symfony\Component\Routing\Route[]
     */
    protected $currentRoutes;

    public function __construct(PagedRouteProviderInterface $pagedRouteProvider, $routesLoadedInParallel = 50)
    {
        $this->provider = $pagedRouteProvider;
        $this->routesLoadedInParallel = $routesLoadedInParallel;
    }

    /**
     * Loads the next routes into the elements array.
     *
     * @param int $offset
     *   The offset used in the db query.
     */
    protected function loadNextElements($offset)
    {
        // Don't ask for routes if there cannot be more. Therefore compare the
        // last loaded routes with the amount loaded each time. In case in the
        // last load could not fulfill the full amount, we can safely assume
        // that there aren't more routes available.
        if (isset($this->currentRoutes) && count($this->currentRoutes) < $this->routesLoadedInParallel)
        {
            $this->currentRoutes = array();
        }
        else {
            $this->currentRoutes = $this->provider->getRoutesPaged($offset, $this->routesLoadedInParallel);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->currentRoutes);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $result = next($this->currentRoutes);
        if ($result === FALSE) {
            $this->loadNextElements($this->current + 1);
        }
        $this->current++;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->currentRoutes);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return key($this->currentRoutes);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
       $this->current = 0;
       $this->loadNextElements($this->current);
    }
}
