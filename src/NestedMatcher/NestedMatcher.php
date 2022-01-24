<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * A more flexible approach to matching. The route collection to match against
 * can be dynamically determined based on the request and users can inject
 * their own filters or use a custom final matching strategy.
 *
 * The nested matcher splits matching into three configurable steps:
 *
 * 1) Get potential matches from a RouteProviderInterface
 * 2) Apply any RouteFilterInterface to reduce the route collection
 * 3) Have FinalMatcherInterface select the best match of the remaining routes
 *
 * @author Larry Garfield
 * @author David Buchmann
 */
final class NestedMatcher implements RequestMatcherInterface
{
    /**
     * The route provider responsible for the first-pass match.
     */
    private RouteProviderInterface $routeProvider;

    private FinalMatcherInterface $finalMatcher;

    /**
     * @var RouteFilterInterface[]
     */
    private array $filters = [];

    /**
     * For caching the sorted $filters.
     *
     * @var RouteFilterInterface[]
     */
    private array $sortedFilters = [];

    public function __construct(RouteProviderInterface $provider, FinalMatcherInterface $final)
    {
        $this->setRouteProvider($provider);
        $this->setFinalMatcher($final);
    }

    public function setRouteProvider(RouteProviderInterface $provider): static
    {
        $this->routeProvider = $provider;

        return $this;
    }

    /**
     * Adds a partial matcher to the matching plan.
     *
     * Partial matchers will be run in the order in which they are added.
     *
     * @param int $priority (optional) The priority of the
     *                      filter. Higher number filters will
     *                      be used first. Defaults to 0
     */
    public function addRouteFilter(RouteFilterInterface $filter, int $priority = 0): static
    {
        if (!\array_key_exists($priority, $this->filters)) {
            $this->filters[$priority] = [];
        }

        $this->filters[$priority][] = $filter;
        $this->sortedFilters = [];

        return $this;
    }

    public function setFinalMatcher(FinalMatcherInterface $final): static
    {
        $this->finalMatcher = $final;

        return $this;
    }

    public function matchRequest(Request $request): array
    {
        $collection = $this->routeProvider->getRouteCollectionForRequest($request);
        if (!count($collection)) {
            throw new ResourceNotFoundException();
        }

        // Route filters are expected to throw an exception themselves if they
        // end up filtering the list down to 0.
        foreach ($this->getRouteFilters() as $filter) {
            $collection = $filter->filter($collection, $request);
        }

        return $this->finalMatcher->finalMatch($collection, $request);
    }

    /**
     * Sorts the filters and flattens them.
     *
     * @return RouteFilterInterface[] the filters ordered by priority
     */
    public function getRouteFilters(): array
    {
        if (0 === count($this->sortedFilters)) {
            $this->sortedFilters = $this->sortFilters();
        }

        return $this->sortedFilters;
    }

    /**
     * Sort filters by priority.
     *
     * The highest priority number is the highest priority (reverse sorting).
     *
     * @return RouteFilterInterface[] the sorted filters
     */
    private function sortFilters(): array
    {
        if (0 === count($this->filters)) {
            return [];
        }

        krsort($this->filters);

        return call_user_func_array('array_merge', $this->filters);
    }
}
