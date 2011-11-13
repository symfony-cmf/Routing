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
     *
     * @return string the controller to use with this route object including
     *      the action, i.e. symfony_cmf_content.controller:indexAction
     *      or false if the resolver can not determine the router
     */
    public function getController(RouteObjectInterface $document);

}
