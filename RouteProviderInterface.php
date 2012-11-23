<?php

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for the route provider the DynamicRouter is using.
 *
 * Typically this could be a doctrine orm or odm repository, but you can
 * implement something else if you need to.
 */
interface RouteProviderInterface
{
    /**
     * Find routes that could match this absolute path.
     *
     * This may return a mixed list of class instances, but all routes returned
     * must extend the core symfony route. The classes may also implement
     * RouteObjectInterface to link to a content document.
     *
     * This method may not throw an exception based on implementation specific
     * restrictions on the url. That case is considered a not found - returning
     * an empty array. Exceptions are only used to abort the whole request in
     * case something is seriously broken, like the storage backend being down.
     *
     * @param string $url
     *
     * @return \Symfony\Component\Routing\RouteCollection with all urls that
     *      could potentially match $url. Empty collection if nothing can match.
     *
     * @throws \Exception if the underlying storage has an error
     */
    public function findManyByUrl($url);

    /**
     * Finds routes that may potentially match the request.
     *
     * Note that implementations may not implement an optimal matching
     * algorithm, simply a reasonable first pass.  That allows for potentially
     * very large route sets to be filtered down to likely candidates, which
     * may then be filtered in memory more completely.
     *
     * @return \Symfony\Component\Routing\RouteCollection with all urls that
     *      could potentially match $request. Empty collection if nothing can
     *      match.
     */
    public function getRouteCollectionForRequest(Request $request);


    /**
     * Find the route using the provided route name (and parameters)
     *
     * @param $name
     * @param array $parameters
     *
     * @return Symfony\Component\Routing\Route
     *
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException if
     *      there is no route with that name in this repository
     */
    public function getRouteByName($name, $parameters = array());
}
