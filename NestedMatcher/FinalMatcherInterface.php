<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * A FinalMatcher returns only one route from a collection of candidate routes.
 */
interface FinalMatcherInterface
{
  /**
   * Matches a request against a route collection and returns exactly one result.
   *
   * @param RouteCollection $collection The collection against which to match.
   * @param Request $request The request to match.
   *
   * @return array An array of parameters
   *
   * @throws ResourceNotFoundException if none of the routes in $collection
   *    matches $request
   *
   * @author Crell
   * @author David Buchmann
   */
  public function finalMatch(RouteCollection $collection, Request $request);
}