<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher as SymfonyUrlMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Extended UrlMatcher to provide an additional interface and enhanced features.
 *
 * @author Larry Garfield
 */
class UrlMatcher extends SymfonyUrlMatcher implements FinalMatcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function finalMatch(RouteCollection $collection, Request $request)
    {
        $this->routes = $collection;
        $context = new RequestContext();
        $context->fromRequest($request);
        $this->setContext($context);
        return $this->match($request->getPathInfo());
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributes(Route $route, $name, array $attributes)
    {
        if ($route instanceof RouteObjectInterface && is_string($route->getRouteKey())) {
            $name = $route->getRouteKey();
        }
        $attributes['_route_name'] = $name;
        $attributes['_route'] = $route;
        return $this->mergeDefaults($attributes, $route->getDefaults());
    }
}
