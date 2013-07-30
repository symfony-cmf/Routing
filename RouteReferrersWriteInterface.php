<?php

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\Route;

/**
 * Interface to be implemented by content that exposes editable route
 * referrers.
 */
interface RouteReferrersWriteInterface extends RouteReferrersInterface
{
    /**
     * Add a route to the collection.
     *
     * @param Route $route
     */
    public function addRoute($route);

    /**
     * Remove a route from the collection.
     *
     * @param Route $route
     */
    public function removeRoute($route);
}
