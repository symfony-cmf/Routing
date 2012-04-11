<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RedirectRouteInterface;

/**
 * Document for redirection entries with the RedirectController.
 *
 * This document may have (in order of precedence):
 *
 * - uri: an absolute uri
 * - routeName and routeParameters: to be used with a routers generate method
 *
 * With standard Symfony routing, you can just use routeName and a hashmap of
 * parameters. For the doctrine router, you need to set the routeTarget but can
 * omit the routeName.
 *
 *
 * @author David Buchmann <david@liip.ch>
 *
 * @PHPCRODM\Document(repositoryClass="Symfony\Cmf\Bundle\ChainRoutingBundle\Document\RouteRepository")
 */
class RedirectRoute extends Route implements RedirectRouteInterface
{
    /**
     * Absolute uri to redirect to
     * @PHPCRODM\Uri
     */
    protected $uri;

    /**
     * The name of a target route (for use with standard symfony routes)
     * @PHPCRODM\String
     */
    protected $routeName;

    /**
     * @PHPCRODM\ReferenceOne
     */
    protected $routeTarget;

    /**
     * Simulate a php hashmap in phpcr. This holds the keys
     *
     * @PHPCRODM\String(multivalue=true)
     */
    protected $parameterKeys;

    /**
     * Simulate a php hashmap in phpcr. This holds the keys
     *
     * @PHPCRODM\String(multivalue=true)
     */
    protected $parameterValues;

    /**
     * {@inheritDoc}
     */
    public function getRouteContent()
    {
        return $this;
    }

    /**
     * Set the route this redirection route points to
     */
    public function setRouteTarget(RouteObjectInterface $document)
    {
        $this->routeTarget = $document;
    }
    /**
     * Get the content document this route entry stands for.
     *
     * If non-null, it is added as route into the parameters, which will lead
     * to have the generate call issued by the RedirectController to have
     * the target route in the parameters.
     *
     * @return RouteObjectInterface the route this redirection points to
     */
    public function getRouteTarget()
    {
        return $this->routeTarget;
    }

    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }
    /**
     * {@inheritDoc}
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param array $parameter a hashmap of key to value mapping for route
     *      parameters
     */
    public function setParameters(array $parameter)
    {
        $this->parameterValues = array_values($parameter);
        $this->parameterKeys = array_keys($parameter);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters()
    {
        $parameters = array();

        if ($this->parameterKeys !== null) {
            array_combine(
                $this->parameterKeys->getValues(),
                $this->parameterValues->getValues()
            );
        }

        $route = $this->getRouteTarget();
        if (! empty($route)) {
            $parameters['route'] = $route;
        }

        return $parameters;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }
    /**
     * {@inheritDoc}
     */
    public function getUri()
    {
        return $this->uri;
    }

    // seems the callbacks are lost when inheriting

    /**
     * @PHPCRODM\PostLoad
     */
    public function initArrays()
    {
        parent::initArrays();
    }

    /**
     * @PHPCRODM\PreUpdate
     * @PHPCRODM\PrePersist
     */
    public function prepareArrays()
    {
        parent::prepareArrays();
    }
}
