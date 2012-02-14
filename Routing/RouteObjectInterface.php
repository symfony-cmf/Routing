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
     * To interoperate with the standard Symfony\Cmf\Bundle\ContentBundle\Document\StaticContent
     * the instance MUST store the property in the field <code>routeContent</code>
     * to have referrer resolution work.
     *
     * @return object the document or entity this route entry points to
     */
    function getRouteContent();

    /**
     * Get the absolute url within the symfony application for this route.
     *
     * Without domain or the eventual app_dev.php
     * In short, this is the url you give the RouteRepositoryInterface to
     * findByUrl
     */
    function getUrl();

    /**
     * Get the configured route parameters.
     *
     * If this array contains _controller, that is used without checking any
     * resolver.
     * To work with the ControllerAliasResolver, this must contain
     * the field 'type' with a value from the controllers_by_alias mapping
     *
     * For multilingual sites, use _locale to set the request language.
     *
     * @return array Information for the ControllerResolver
     */
    function getRouteDefaults();
}
