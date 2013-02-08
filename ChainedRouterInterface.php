<?php

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\RouterInterface;

/**
 * Interface to combine the VersatileGeneratorInterface with the RouterInterface
 */
interface ChainedRouterInterface extends RouterInterface, VersatileGeneratorInterface
{
    /**
     * Convert a route identifier (name, content object etc) into a string
     * usable for logging and other debug/error messages
     *
     * @param mixed $name
     * @param array $parameters which should contain a content field containing a RouteAwareInterface object
     * @return string
     */
    public function getRouteName($name, $parameters = array());
}