<?php

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\RouterInterface;

interface ChainedRouterInterface extends RouterInterface
{
    /**
     * This method checks if the current router supports the passed object
     *
     * @param $name mixed The route name or route object
     *
     * @return bool
     */
    function supports($name);
}