<?php

namespace Symfony\Cmf\Component\Routing\NestedMatcher;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;

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
        if (!empty($attributes['_route'])) {
            if (empty($attributes['_route_name']) && is_string($attributes['_route'])) {
                $attributes['_route_name'] = $attributes['_route'];
            }

            if (! $attributes['_route'] instanceof Route) {
                $attributes['_route'] = $collection->get($attributes['_route']);
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
