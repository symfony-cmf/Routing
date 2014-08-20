<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * ChainRouterInterface
 *
 * Allows access to a lot of different routers.
 *
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 * @author Magnus Nordlander <magnus@e-butik.se>
 */
interface ChainRouterInterface extends RouterInterface, RequestMatcherInterface
{
    /**
     * Add a Router to the index
     *
     * @param RouterInterface $router   The router instance
     * @param integer         $priority The priority
     */
    public function add(RouterInterface $router, $priority = 0);

    /**
     * Sorts the routers and flattens them.
     *
     * @return RouterInterface[]
     */
    public function all();
}
