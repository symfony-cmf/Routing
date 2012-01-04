<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * ChainRouter
 *
 * Allows access to a lot of different routers.
 *
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 * @author Magnus Nordlander <magnus@e-butik.se>
 */
class ChainRouter implements RouterInterface, WarmableInterface
{
    private $context;
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function getContext()
    {
        return $this->context;
    }

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
            $routers = array_merge($routers, $all);
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
        $methodNotAllowed = null;

        foreach ($this->all() as $router) {
            try {
                return $router->match($url);
            } catch (ResourceNotFoundException $e) {
                if ($this->logger) {
                    $this->logger->addError($e);
                }
                // Needs special care
            } catch (MethodNotAllowedException $e) {
                if ($this->logger) {
                    $this->logger->addError($e);
                }
                $methodNotAllowed = $e;
            }
        }

        throw $methodNotAllowed ?: new ResourceNotFoundException("None of the routers in the chain matched '$url'");
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
            } catch (RouteNotFoundException $e) {
                if ($this->logger) {
                    $this->logger->addError($e);
                }
            }
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
        $this->context = $context;

        foreach ($this->all() as $router) {
            if ($router instanceof RequestContextAwareInterface) {
                $router->setContext($context);
            }
        }
    }

    /**
     * check for each contained router if it can warmup
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->all() as $router) {
            if ($router instanceof WarmableInterface) {
                $router->warmUp($cacheDir);
            }
        }
    }

    public function getRouteCollection()
    {
        // TODO: is this the right thing? can we optimize?
        $collection = new \Symfony\Component\Routing\RouteCollection();
        foreach ($this->all() as $router) {
            $collection->addCollection($router->getRouteCollection());
        }
        return $collection;
    }
}
