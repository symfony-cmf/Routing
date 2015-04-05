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
 * @see Symfony\Component\Routing\Generator\UrlGeneratorInterface::generate()
 */
class RouterGenerateEvent extends Event
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var bool
     */
    protected $absolute;

    /**
     * @param string $name 
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name 
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters 
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return bool
     */
    public function isAbsolute()
    {
        return $this->absolute;
    }

    /**
     * @param bool $absolute
     */
    public function setAbsolute($absolute)
    {
        $this->absolute = $absolute;
    }
}
