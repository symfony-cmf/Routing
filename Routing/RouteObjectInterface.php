<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

/**
 * Documents for entries in the routing table need to implement this interface
 * so the ContentRouter can handle them.
 */
interface RouteObjectInterface
{
    /**
     * @return object the document or entity this route entry points to
     */
    function getReference();

    /**
     * To work with the default ControllerResolver, this must at least contain
     * the field 'type' with a value from the controllers_by_alias mapping
     *
     * @return array Information for the ControllerResolver
     */
    function getRouteDefaults();
}
