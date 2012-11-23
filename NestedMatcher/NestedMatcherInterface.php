<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * A NestedMatcher allows for multiple-stage resolution of a route.
 */
interface NestedMatcherInterface extends RequestMatcherInterface {

  /**
   * Sets the first matcher for the matching plan.
   *
   * Partial matchers will be run in the order in which they are added.
   *
   * @param \Drupal\Core\Routing\InitialMatcherInterface $matcher
   *   An initial matcher.  It is responsible for its own configuration and
   *   initial route collection
   *
   * @return \Drupal\Core\Routing\NestedMatcherInterface
   *   The current matcher.
   */
  public function setInitialMatcher(InitialMatcherInterface $initial);

  /**
   * Adds a route filter to the matching plan.
   *
   * Route Filters will be run in the order in which they are added.
   *
   * @param RouteFilterInterface $filter
   *   A route filter.
   * @param int $priority
   *   (optional) The priority of the filter. Higher number matchers will be checked
   *   first. Default to 0.
   *
   * @return NestedMatcherInterface
   *   The current matcher.
   */
  public function addRouteFilter(RouteFilterInterface $filter, $priority = 0);

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
  public function setFinalMatcher(FinalMatcherInterface $final);
}
