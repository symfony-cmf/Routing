<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

/**
 * Interface for the route provider the DoctrineRouter is using.
 * Typically this could be a doctrine orm or odm repository,
 * but you can implement something else if you need to
 */
interface RouteRepositoryInterface
{
    /**
     * Find the route that represents this absolute url.
     *
     * This method may not throw an exception based on implementation specific
     * restrictions on the url. That case is considered a not found - returning
     * null. Exceptions are only used to abort the whole request in case
     * something is seriously broken.
     *
     * @param string $url
     *
     * @return RouteObjectInterface or null if no route found with that url
     *
     * @throws \Exception if the underlying storage has an error
     */
    function findByUrl($url);
}
