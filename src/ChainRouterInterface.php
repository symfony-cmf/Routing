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

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Interface for a router that proxies routing to other routers.
 *
 * @author Daniel Wehner <dawehner@googlemail.com>
 */
interface ChainRouterInterface extends RouterInterface, RequestMatcherInterface
{
    public function add(RouterInterface|RequestMatcherInterface|UrlGeneratorInterface $router, int $priority = 0): void;

    /**
     * Sorts the routers and flattens them.
     *
     * @return array<RouterInterface|RequestMatcherInterface|UrlGeneratorInterface>
     */
    public function all(): array;
}
