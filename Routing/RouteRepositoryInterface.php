<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

/**
 * Interface for the route provider the DoctrineRouter is using.
 *
 * Typically this could be a doctrine orm or odm repository, but you can
 * implement something else if you need to.
 */
interface RouteRepositoryInterface
{
    /**
     * Find routes that could match this absolute path.
     *
     * This may return a mixed list of class instances, but all routes returned
     * must extend the core symfony route.
     *
     * This method may not throw an exception based on implementation specific
     * restrictions on the url. That case is considered a not found - returning
     * an empty array. Exceptions are only used to abort the whole request in
     * case something is seriously broken, like the storage backend being down.
     *
     * @param string $url
     *
     * @return array of Symfony\Component\Routing\Route with all urls that
     *      could potentially match $url. Empty array if nothing can match.
     *
     * @throws \Exception if the underlying storage has an error
     */
    function findManyByUrl($url);
}
