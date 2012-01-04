<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

/**
 * Additional methods needed by teh RedirectController to redirect based on
 * the route
 */
interface RedirectRouteInterface extends RouteObjectInterface
{
    /**
     * Get the absolute uri to redirect to external domains.
     *
     * If this is non-empty, the other methods won't be used.
     *
     * @return string target absolute uri
     */
    public function getUri();

    /**
     * Get the name of the target route.
     *
     * @return string target route name
     */
    function getRouteName();

    /**
     * Get the parameters for router::generate()
     *
     * Note that for the DoctrineRouter, you can return the target
     * route object as field 'route' of the hashmap
     *
     * @return array Information to build the route
     */
    public function getParameters();

}
