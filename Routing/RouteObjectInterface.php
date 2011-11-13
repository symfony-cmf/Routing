<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

/**
 * Documents for entries in the routing table need to implement this interface
 * so the DoctrineRouter can handle them.
 */
interface RouteObjectInterface
{
    /**
     * Get the content document this route entry stands for. If non-null,
     * the ControllerClassResolver uses it to identify a controller and
     * the content is passed to the controller.
     *
     * If there is no specific content for this url (i.e. its an "application"
     * page), may return null.
     *
     * @return object the document or entity this route entry points to
     */
    function getReference();

    /**
     * To work with the ControllerAliasResolver, this must at least contain
     * the field 'type' with a value from the controllers_by_alias mapping
     *
     * @return array Information for the ControllerResolver
     */
    function getRouteDefaults();
}
