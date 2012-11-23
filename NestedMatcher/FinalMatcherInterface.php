<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * A FinalMatcher returns only one route from a collection of candidate routes.
 */
interface FinalMatcherInterface {

  /**
   * Matchs a request against a route collection.
   *
   * @param RouteCollection $collection
   *   The collection against which to match.
   * @param Request $request
   *   The request to match.
   *
   * @return array An array of parameters
   */
  public function finalMatch(RouteCollection $collection, Request $request);
}
