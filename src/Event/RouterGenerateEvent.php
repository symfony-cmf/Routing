<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Event;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route;

/**
 * Event fired before the dynamic router generates a url for a route.
 *
 * The name, parameters and absolute properties have the semantics of
 * UrlGeneratorInterface::generate()
 *
 * @author Ben Glassman
 *
 * @see \Symfony\Component\Routing\Generator\UrlGeneratorInterface::generate()
 */
class RouterGenerateEvent extends BcEvent
{
    /**
     * The name of the route or the Route instance to generate.
     *
     * @var string|Route
     */
    private $route;

    /**
     * The parameters to use when generating the url.
     *
     * @var array
     */
    private $parameters;

    /**
     * The type of reference to be generated (one of the constants in UrlGeneratorInterface).
     *
     * @var bool|string
     */
    private $referenceType;

    /**
     * @param string|Route $route         The route name or object
     * @param array        $parameters    The parameters to use
     * @param bool|string  $referenceType The type of reference to be generated
     */
    public function __construct($route, $parameters, $referenceType)
    {
        if (is_object($route)) {
            @trigger_error(sprintf('Passing an object as the route name is deprecated in symfony-cmf/Routing v2.2 and will not work in Symfony 5.0. Pass the `RouteObjectInterface::OBJECT_BASED_ROUTE_NAME` constant as the route name and the route object as "%s" parameter in the parameters array.', RouteObjectInterface::ROUTE_OBJECT), E_USER_DEPRECATED);
        }

        $this->route = $route;
        $this->parameters = $parameters;
        $this->referenceType = $referenceType;
    }

    /**
     * Get route name or object.
     *
     * @return string|Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set route name or object.
     *
     * @param string|Route $route
     */
    public function setRoute($route)
    {
        if (is_object($route)) {
            @trigger_error(sprintf('Passing an object as the route name is deprecated in symfony-cmf/Routing v2.2 and will not work in Symfony 5.0. Pass the `RouteObjectInterface::OBJECT_BASED_ROUTE_NAME` constant as the route name and the route object as "%s" parameter in the parameters array.', RouteObjectInterface::ROUTE_OBJECT), E_USER_DEPRECATED);
        }

        $this->route = $route;
    }

    /**
     * Get route parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set the route parameters.
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Set a route parameter.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Remove a route parameter by key.
     *
     * @param string $key
     */
    public function removeParameter($key)
    {
        unset($this->parameters[$key]);
    }

    /**
     * The type of reference to be generated (one of the constants in UrlGeneratorInterface).
     *
     * @return bool|string
     */
    public function getReferenceType()
    {
        return $this->referenceType;
    }

    /**
     * The type of reference to be generated (one of the constants in UrlGeneratorInterface).
     *
     * @param bool|string $referenceType
     */
    public function setReferenceType($referenceType)
    {
        $this->referenceType = $referenceType;
    }
}
