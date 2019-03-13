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

use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Interface for a router that proxies routing to other routers.
 *
 * @author Daniel Wehner <dawehner@googlemail.com>
 */
interface ChainRouterInterface extends RouterInterface, RequestMatcherInterface
{
    /**
     * Add a Router to the index.
     *
     * @param RouterInterface $router   The router instance. Instead of RouterInterface, may also
     *                                  be RequestMatcherInterface and UrlGeneratorInterface
     * @param int             $priority The priority
     */
    public function add($router, $priority = 0);

    /**
     * Sorts the routers and flattens them.
     *
     * @return RouterInterface[] or RequestMatcherInterface and UrlGeneratorInterface
     */
    public function all();
}
