<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\UrlMatcher as SymfonyUrlMatcher;

/**
 * Extended UrlMatcher to provide an additional interface and enhanced features.
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
    protected function getAttributes(Route $route)
    {
        $args = func_get_args();
        array_shift($args);
        $args[] = array('_name' => $name, '_route' => $route);
        return $this->mergeDefaults(call_user_func_array('array_replace', $args), $route->getDefaults());
    }
}
