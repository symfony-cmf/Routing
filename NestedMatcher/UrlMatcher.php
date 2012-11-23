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

    // The redefined $routes property and the constructor are only needed
    // until Symfony makes $routes protected.
    // See: https://github.com/symfony/symfony/pull/6100

    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * Constructor.
     *
     * @param RouteCollection $routes  A RouteCollection instance
     * @param RequestContext  $context The context
     *
     * @api
     */
    public function __construct(RouteCollection $routes, RequestContext $context)
    {
        $this->routes = $routes;
        $this->context = $context;
    }


    /**
     * Sets the route collection this matcher should use.
     *
     * @param \Symfony\Component\Routing\RouteCollection $collection
     *   The collection against which to match.
     *
     * @return \Drupal\Core\Routing\FinalMatcherInterface
     *   The current matcher.
     */
    public function setCollection(RouteCollection $collection)
    {
        $this->routes = $collection;
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
