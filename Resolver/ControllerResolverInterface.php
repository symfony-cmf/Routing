<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;

/**
 * Interface for all controller resolvers that work with the DoctrineRouter
 *
 * @author David Buchmann
 */
interface ControllerResolverInterface
{
    /**
     * Retrieves the right controller for the given route $document.
     *
     * @param RouteObjectInterface $document the document or entity for the route
     * @param array $defaults the getRouteDefaults array which may be altered by
     *      the resolver
     *
     * @return string the controller to use with this route object including
     *      the action, i.e. symfony_cmf_content.controller:indexAction
     *      or false if the resolver can not determine the router
     */
    function getController(RouteObjectInterface $document, array &$defaults);

}
