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

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher as SymfonyUrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Extended UrlMatcher to provide an additional interface and enhanced features.
 *
 * @author Larry Garfield
 */
class UrlMatcher extends SymfonyUrlMatcher implements FinalMatcherInterface
{
    public function finalMatch(RouteCollection $collection, Request $request): array
    {
        $this->routes = $collection;
        $context = new RequestContext();
        $context->fromRequest($request);
        $this->setContext($context);

        return $this->match($request->getPathInfo());
    }

    protected function getAttributes(Route $route, string $name, array $attributes): array
    {
        if ($route instanceof RouteObjectInterface && is_string($route->getRouteKey())) {
            $name = $route->getRouteKey();
        }
        $attributes[RouteObjectInterface::ROUTE_NAME] = $name;
        $attributes[RouteObjectInterface::ROUTE_OBJECT] = $route;

        return $this->mergeDefaults($attributes, $route->getDefaults());
    }
}
