<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\HttpFoundation\Request;

/**
 * A PartialMatcher works like a UrlMatcher, but will return multiple candidate routes.
 */
interface InitialMatcherInterface {

  /**
   * Matches a request against multiple routes.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A Request object against which to match.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   A RouteCollection of matched routes.
   */
  public function matchRequestPartial(Request $request);
}
