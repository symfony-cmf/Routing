<?php

namespace Symfony\Cmf\Component\Routing;

/**
 * Interface for a provider which allows to retrieve a limited amount of routes.
 */
interface RangedRouteProviderInterface extends RouteProviderInterface
{
    /**
     * Find an amount of routes with an offset and possible a limit.
     *
     * In case you want to iterate over all routes, you want to avoid to load
     * all routes at once.
     *
     * @param int $offset
     *   The sequence will start with that offset in the list of all routes.
     * @param int $length [optional]
     *   The sequence will have that many routes in it.
     *
     * @return \Symfony\Component\Routing\Route[]
     *   Routes keyed by the route name.
     */
    public function getRoutesRanged($offset, $length = NULL);
}
