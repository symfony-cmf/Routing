<?php

namespace Symfony\Cmf\Component\Routing;

/**
 * Interface to be implemented by content that wants to be compatible with the
 * DynamicRouter
 */
interface RouteAwareInterface
{
    /**
     * Get the routes that point to this content.
     *
     * Note: For PHPCR, as explained in RouteObjectInterface the route must use
     * the routeContent field to store the reference to the content so you can
     * get the routes with Referrers(filter="routeContent")
     *
     * @return array of RouteObjectInterface instances that point to this content
     */
    function getRoutes();
}

