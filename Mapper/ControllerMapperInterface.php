<?php

namespace Symfony\Cmf\Component\Routing\Mapper;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Interface for all controller mappers that work with the DoctrineRouter
 *
 * @author David Buchmann
 */
interface ControllerMapperInterface
{
    /**
     * Retrieves the right controller for the given route $document.
     *
     * @param RouteObjectInterface $document the document or entity for the route
     * @param array $defaults the getRouteDefaults array which may be altered by
     *      the mapper
     *
     * @return string the controller to use with this route object including
     *      the action, i.e. symfony_cmf_content.controller:indexAction
     *      or false if the mapper can not determine the router
     */
    function getController(RouteObjectInterface $document, array &$defaults);

}
