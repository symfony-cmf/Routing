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

/**
 * This generator can provide additional information about the route that we wanted to generate.
 */
interface VersatileGeneratorInterface extends UrlGeneratorInterface
{
    /**
     * Whether this generator supports the supplied $name.
     *
     * This check does not need to look if the specific instance can be
     * resolved to a route, only whether the router can generate routes from
     * objects of this class.
     *
     * @deprecated This method is deprecated since version 2.3 and will be
     * removed in version 3.O.
     *
     * This method was used to not call generators that can not handle objects
     * in $name. With Symfony 5, this becomes obsolete as the strict type
     * declaration prevents passing anything else than a string as $name.
     *
     * @param mixed $name The route "name" which may also be an object or anything
     *
     * @return bool
     */
    public function supports($name);

    /**
     * Convert a route identifier (name, content object etc) into a string
     * usable for logging and other debug/error messages.
     *
     * @param mixed $name       In Symfony 5, the name can only be a string
     * @param array $parameters Which might hold a route object or content id or similar to include in the debug message
     *
     * @return string
     */
    public function getRouteDebugMessage($name, array $parameters = []);
}
