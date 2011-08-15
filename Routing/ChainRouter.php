<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;


/**
 * ChainRouter
 *
 * Allows access to a lot of different routers.
 *
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 * @author Magnus Nordlander <magnus@e-butik.se>
 */
class ChainRouter implements RouterInterface
{
    /**
     * @var array
     */
    private $routers = array();

    /**
     * Add a Router to the index
     *
     * @param RouterInterface $router
     * @param integer $priority
     */
    public function add(RouterInterface $router, $priority = 0)
    {
        if (empty($this->routers[$priority])) {
            $this->routers[$priority] = array();
        }

        $this->routers[$priority][] = $router;
    }

    /**
     * Sorts the routers and flattens them.
     *
     * @return array
     */
    public function all()
    {
        krsort($this->routers);
        $routers = array();

        foreach ($this->routers as $all) {
            $routers = array_merge($routers, array($all));
        }

        return $routers;
    }

    /**
     * Loops through all routes and tries to match the passed url.
     *
     * @param string $url
     * @throws ResourceNotFoundException $e
     * @throws MethodNotAlloedException $e
     * @return array
     */
    public function match($url)
    {
        $methodNotFound = null;

        foreach ($this->all() as $router) {
            try {
                return $router->match($url);
            } catch (ResourceNotFoundException $e) {
                // Needs special care
            } catch (MethodNotAllowedException $e) {
                $methodNotFound = $e;
            }
        }

        throw $methodNotFound ?: new ResourceNotFoundException();
    }

    /**
     * Loops through all registered routers and returns a router if one is found.
     * It will always return the first route generated.
     *
     * @param string $name
     * @param array $paramaters
     * @param Boolean $absolute
     * @throws RouteNotFoundException
     * @return string
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        foreach ($this->all() as $router) {
            try {
                return $router->generate($name, $parameters, $absolute);
            } catch (RouteNotFoundException $e) { }
        }

        throw new RouteNotFoundException(sprintf('Route "%s" does not exist.', $name));
    }

    /**
     * Sets the Request Context
     *
     * @param RouterContext $context
     */
    public function setContext(RequestContext $context)
    {
        foreach ($this->all() as $router) {
            if ($router instanceof RequestContextAwareInterface) {
                $router->setContext($context);
            }
        }
    }
}
