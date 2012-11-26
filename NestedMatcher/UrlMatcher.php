<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher as SymfonyUrlMatcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * Extended UrlMatcher to provide an additional interface and enhanced features.
 *
 * @author Crell
 */
class UrlMatcher extends SymfonyUrlMatcher implements FinalMatcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function finalMatch(RouteCollection $collection, Request $request)
    {
        $this->routes = $collection;
        $this->match($request->getPathInfo());
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributes(Route $route, $name, $attributes)
    {
        $attributes['_name'] = $name;
        $attributes['_route'] = $route;
        return $this->mergeDefaults($attributes, $route->getDefaults());
    }
}
