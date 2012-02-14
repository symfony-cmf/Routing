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
     * Find the route that represents this absolute url
     *
     * @param string $url
     *
     * @return RouteObjectInterface
     */
    function findByUrl($url);
}
