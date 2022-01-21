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
     * Convert a route identifier (name, content object etc) into a string
     * usable for logging and other debug/error messages.
     *
     * @param array<string, mixed> $parameters Which might hold a route object or content id or similar to include in the debug message
     */
    public function getRouteDebugMessage(string $name, array $parameters = []): string;
}
