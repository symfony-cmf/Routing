<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\Route;

/**
 * Interface to be implemented by content that exposes editable route
 * referrers.
 */
interface RouteReferrersInterface extends RouteReferrersReadInterface
{
    /**
     * Add a route to the collection.
     */
    public function addRoute(Route $route): void;

    /**
     * Remove a route from the collection.
     */
    public function removeRoute(Route $route): void;
}
