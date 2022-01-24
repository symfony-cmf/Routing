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

use Symfony\Component\Routing\Route as SymfonyRoute;

/**
 * Provides a route collection which avoids having all routes in memory.
 *
 * Internally, this does load multiple routes over time using a
 * PagedRouteProviderInterface $route_provider.
 */
class PagedRouteCollection implements \Iterator, \Countable
{
    private PagedRouteProviderInterface $provider;

    /**
     * Stores the amount of routes which are loaded in parallel and kept in
     * memory.
     */
    private int $routesBatchSize;

    /**
     * Contains the current item the iterator points to.
     */
    private int $current = -1;

    /**
     * Stores the current loaded routes.
     *
     * @var SymfonyRoute[]
     */
    private ?array $currentRoutes = null;

    public function __construct(PagedRouteProviderInterface $pagedRouteProvider, int $routesBatchSize = 50)
    {
        $this->provider = $pagedRouteProvider;
        $this->routesBatchSize = $routesBatchSize;
    }

    private function loadNextElements(int $offset): void
    {
        // If the last batch was smaller than the batch size, this means there
        // are no more routes available.
        if (isset($this->currentRoutes) && count($this->currentRoutes) < $this->routesBatchSize) {
            $this->currentRoutes = [];
        } else {
            $this->currentRoutes = $this->provider->getRoutesPaged($offset, $this->routesBatchSize);
        }
    }

    public function current(): bool|SymfonyRoute
    {
        return current($this->currentRoutes);
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        $result = next($this->currentRoutes);
        if (false === $result) {
            $this->loadNextElements($this->current + 1);
        }
        ++$this->current;
    }

    public function key(): string|int|null
    {
        return key($this->currentRoutes);
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return null !== key($this->currentRoutes);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->current = 0;
        $this->currentRoutes = null;
        $this->loadNextElements($this->current);
    }

    /**
     * Gets the number of Routes in this collection.
     */
    public function count(): int
    {
        return $this->provider->getRoutesCount();
    }
}
