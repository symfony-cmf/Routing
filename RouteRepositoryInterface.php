<?php

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for the route provider the DynamicRouter is using.
 *
 * Typically this could be a doctrine orm or odm repository, but you can
 * implement something else if you need to.
 */
interface RouteRepositoryInterface
{
    /**
     * Provide routes that could match this absolute path.
     *
     * This may return a mixed list of class instances, but all routes returned
     * must be instanceof the core symfony route. The classes may also implement
     * RouteObjectInterface to link to a content document.
     *
     * This method may not throw an exception based on implementation specific
     * restrictions on the url. That case is considered a not found - returning
     * an empty collection. Exceptions are only used to abort the whole request in
     * case something is seriously broken, like the storage backend being down.
     *
     * @param Request $request
     *
     * @return RouteCollection with all urls that
     *      could potentially match $url. Empty collection if nothing can match.
     *
     * @throws \Exception if the underlying storage has an error
     */
    public function getRouteCollectionForRequest(Request $request);

    /**
     * Find the route using the provided route name (and parameters)
     *
     * @param mixed $name
     * @param array $parameters
     *
     * @return Route
     *
     * @throws RouteNotFoundException if there is no route with that name in
     *      this repository
     */
    public function getRouteByName($name, $parameters = array());
}
