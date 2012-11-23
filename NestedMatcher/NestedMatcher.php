<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;

/**
 * The nested matcher layers multiple partial matchers together.
 */
class NestedMatcher implements RequestMatcherInterface {

    /**
     * The final matcher.
     *
     * This object must also match either RequestMatcherInterface or
     * UrlMatcherInterface.
     *
     * @var FinalMatcherInterface
     */
    protected $finalMatcher;

    /**
     * An array of RouteFilterInterface objects.
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Array of RouteFilterInterface objects, sorted.
     *
     * @var type
     */
    protected $sortedFilters = array();

    /**
     * The route provider responsible for the first-pass match.
     *
     * @var RouteProviderInterface
     */
    protected $routeProvider;

    /**
     * Constructs a new NestedMatcher
     *
     * @param RouteProviderInterface $provider
     *   The Route Provider this matcher should use.
     */
    public function __construct(RouteProviderInterface $provider) {
      $this->routeProvider = $provider;
    }

    /**
     * Adds a partial matcher to the matching plan.
     *
     * Partial matchers will be run in the order in which they are added.
     *
     * @param \Drupal\Core\Routing\PartialMatcherInterface $matcher
     *   A partial matcher.
     * @param int $priority
     *   (optional) The priority of the matcher. Higher number matchers will be checked
     *   first. Default to 0.
     *
     * @return NestedMatcherInterface
     *   The current matcher.
     */
    public function addRouteFilter(RouteFilterInterface $filter, $priority = 0) {
      if (empty($this->filters[$priority])) {
        $this->filters[$priority] = array();
      }

      $this->filter[$priority][] = $filter;
      $this->sortedFilters = array();
    }

    /**
     * Sets the final matcher for the matching plan.
     *
     * @param \Drupal\Core\Routing\FinalMatcherInterface $final
     *   The matcher that will be called last to ensure only a single route is
     *   found.
     *
     * @return \Drupal\Core\Routing\NestedMatcherInterface
     *   The current matcher.
     */
    public function setFinalMatcher(FinalMatcherInterface $final) {
      $this->finalMatcher = $final;

      return $this;
    }

    /**
     * Sets the route provider for the matching plan.
     *
     * @param RouteProviderInterface $provider
     *   A route provider.  It is responsible for its own configuration.
     *
     * @return NestedMatcherInterface
     *   The current matcher.
     */
    public function setRouteProvider(RouteProviderInterface $provider) {
      $this->routeProvider = $provider;

      return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function matchRequest(Request $request) {

      $collection = $this->routeProvider->getRouteCollectionForRequest($request);

      if (!count($collection)) {
        throw new ResourceNotFoundException();
      }

      // Route Filters are expected to throw an exception themselves if they
      // end up filtering the list down to 0.
      foreach ($this->getRouteFilters() as $filter) {
        $collection = $filter->filter($collection, $request);
      }

      $this->finalMatcher->setCollection($collection);

      if ($this->finalMatcher instanceof RequestMatcherInterface) {
        $attributes = $this->finalMatcher->matchRequest($request);
      }
      else {
        $context = new RequestConext();
        $context->fromRequest($request);
        $this->finalMatcher->setContext($context);
        $attributes = $this->finalMatcher->match($this->getRequestPath($request));
      }

      return $attributes;
    }

    /**
     * Retrieves the path to match against from the request in a UrlMatcher.
     *
     * (This is the part Drupal would override.)
     *
     * @param Request $request
     *
     * @return string The path to match against
     */
    protected function getRequestPath(Request $request) {
      return $request->getPathInfo();
    }

    /**
     * Sorts the filters and flattens them.
     *
     * @return array
     *   An array of RequestMatcherInterface objects.
     */
    public function getRouteFilters() {
      if (empty($this->sortedFilters)) {
        $this->sortedFilters = $this->sortFilters();
      }

      return $this->sortedMatchers;
    }

    /**
     * Sort filters by priority.
     *
     * The highest priority number is the highest priority (reverse sorting).
     *
     * @return \Symfony\Component\Routing\RequestMatcherInterface[]
     *   An array of Matcher objects in the order they should be used.
     */
    protected function sortFilters() {
      $sortedFilters = array();
      krsort($this->filters);

      foreach ($this->filters as $filters) {
        $sortedFilters = array_merge($sortedFilters, $filters);
      }

      return $sortedFilters;
    }
}
