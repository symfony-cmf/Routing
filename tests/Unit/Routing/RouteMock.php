<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Unit\Routing;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;

class RouteMock extends SymfonyRoute implements RouteObjectInterface
{
    private $locale;

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getContent()
    {
        return;
    }

    public function getDefaults()
    {
        $defaults = [];
        if (null !== $this->locale) {
            $defaults['_locale'] = $this->locale;
        }

        return $defaults;
    }

    public function getRequirement($key)
    {
        if ('_locale' !== $key) {
            throw new \Exception();
        }

        return $this->locale;
    }

    public function getRouteKey()
    {
        return;
    }
}
