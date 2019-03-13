<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Unit\Enhancer;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route;

/**
 * Empty abstract class to be able to mock an object that both extends Route
 * and implements RouteObjectInterface.
 */
abstract class RouteObject extends Route implements RouteObjectInterface
{
    public function getRouteKey()
    {
    }
}
