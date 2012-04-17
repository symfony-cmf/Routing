<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RedirectRouteInterface;

/**
 * {@inheritDoc}
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
     * @PHPCRODM\Boolean
     */
    protected $permanent;

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

    public function setRouteContent($document)
    {
        throw new \LogicException('Do not set a content for the redirect route. It is its own content.');
    }

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
     * Get the target route document this route redirects to.
     *
     * If non-null, it is added as route into the parameters, which will lead
     * to have the generate call issued by the RedirectController to have
     * the target route in the parameters.
     *
     * @return RouteObjectInterface the route this redirection points to
     *
     * @see getParameters
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
     * Set whether this redirection should be permanent or not.
     *
     * @param boolean $permanent
     */
    public function setPermanent($permanent)
    {
        $this->permanent = $permanent;
    }

    /**
     * {@inheritDoc}
     */
    public function isPermanent()
    {
        return $this->permanent;
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
            $parameters = array_combine(
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
}
