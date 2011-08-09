<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

interface RouteObjectInterface
{
    function getReference();

    function getRouteDefaults();
}
