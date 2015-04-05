<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event fired before the dynamic router generates a url for a route
 * The name, parameters and absolute properties are used by the url generator
 *
 * @author Ben Glassman
 * @see Symfony\Component\Routing\Generator\UrlGeneratorInterface::generate()
 */
class RouterGenerateEvent extends Event
{
    /**
     * The name of the route or the Route instance
     * 
     * @var string|Symfony\Component\Routing\Route
     */
    private $name;

    /**
     * The parameters to use when generating the url
     * 
     * @var array
     */
    private $parameters;

    /**
     * Whether the generated url should be absolute
     * 
     * @var array
     */
    private $absolute;

    /**
     * @param string|Symfony\Component\Routing\Route $name 
     * @param array $parameters 
     * @param bool $absolute 
     */
    public function __construct($name, $parameters, $absolute)
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->absolute = $absolute;
    }

    /**
     * Get route name
     * 
     * @return string|Symfony\Component\Routing\Route
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set route name
     * 
     * @param string|Symfony\Component\Routing\Route $name 
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get route parameters
     * 
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set the route parameters
     *
     * @param array $parameters 
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Set a route parameter
     * 
     * @param string $key 
     * @param mixed $value 
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Remove a route parameter by key
     * 
     * @param string $key 
     */
    public function removeParameter($key)
    {
        unset($this->parameters[$key]);
    }

    /**
     * Should the generated url be absolute
     * 
     * @return bool
     */
    public function isAbsolute()
    {
        return $this->absolute;
    }

    /**
     * Set whether the generated url should be absolute
     * 
     * @param bool $absolute 
     */
    public function setAbsolute($absolute)
    {
        $this->absolute = $absolute;
    }
}
