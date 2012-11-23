<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

/**
 * A RouteFilter takes a RouteCollection and returns a filtered subset.
 *
 * It is not implemented as a filter iterator because we want to allow
 * router filters to handle their own empty-case handling, usually by throwing
 * an appropriate exception if no routes match the object's rules.
 *
 */
interface RouteFilterInterface
{
    /**
     * Matches a request against multiple routes.
     *
     * @param RouteCollection $collection
     *    The collection against which to match.
     * @param Request $request
     *   A Request object against which to match.
     *
     * @return RouteCollection
     *   A RouteCollection of matched routes.
     */
    public function filter(RouteCollection $collection, Request $request);
}
