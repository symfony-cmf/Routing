<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\Route as SymfonyRoute;

/**
 * Interface for a provider which allows to retrieve a limited amount of routes.
 */
interface PagedRouteProviderInterface extends RouteProviderInterface
{
    /**
     * Find an amount of routes with an offset and possible a limit.
     *
     * In case you want to iterate over all routes, you want to avoid to load
     * all routes at once.
     *
     * @param int  $offset The sequence will start with that offset in the list of all routes
     * @param ?int $length The sequence will have that many routes in it. If no length is
     *                     specified all routes are returned
     *
     * @return SymfonyRoute[] Routes keyed by the route name
     */
    public function getRoutesPaged(int $offset, ?int $length = null): array;

    /**
     * Determines the total amount of routes.
     */
    public function getRoutesCount(): int;
}
