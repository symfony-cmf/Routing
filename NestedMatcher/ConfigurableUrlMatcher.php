<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\NestedMatcher\FinalMatcherInterface;

/**
 * A final matcher that can proxy any matcher having the right constructor
 * signature, the same way the symfony core Router class does.
 *
 * @author DavidBuchmann
 */
class ConfigurableUrlMatcher implements FinalMatcherInterface
{
    private $matcherClass;

    public function __construct($matcherClass = 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher')
    {
        $this->matcherClass = $matcherClass;
    }

    /**
     * {@inheritdoc}
     */
    public function finalMatch(RouteCollection $collection, Request $request)
    {
        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = $this->getMatcher($collection, $context);
        $attributes = $matcher->match($request->getPathInfo());

        // cleanup route attributes
        if (! isset($attributes[RouteObjectInterface::ROUTE_OBJECT])
            || ! $attributes[RouteObjectInterface::ROUTE_OBJECT] instanceof Route
        ) {
            $name = $attributes['_route']; // this is the field coming from symfony core
            $route = $collection->get($name);
            $attributes[RouteObjectInterface::ROUTE_OBJECT] = $route;

            if ($route instanceof RouteObjectInterface && is_string($route->getRouteKey())) {
                $name = $route->getRouteKey();
            }

            if (is_string($name)) {
                $attributes[RouteObjectInterface::ROUTE_NAME] = $name;
            }
        }

        return $attributes;
    }

    /**
     * @param RouteCollection $collection the route collection to match
     * @param RequestContext  $context      the context to match in
     *
     * @return \Symfony\Component\Routing\Matcher\UrlMatcherInterface
     */
    protected function getMatcher(RouteCollection $collection, RequestContext $context)
    {
        return new $this->matcherClass($collection, $context);
    }
}
