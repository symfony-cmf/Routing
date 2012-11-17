<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * A FinalMatcher returns only one route from a collection of candidate routes.
 */
interface FinalMatcherInterface extends RequestMatcherInterface {

  /**
   * Sets the route collection this matcher should use.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The collection against which to match.
   *
   * @return \Drupal\Core\Routing\FinalMatcherInterface
   *   The current matcher.
   */
  public function setCollection(RouteCollection $collection);
}
