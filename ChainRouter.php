<?php

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\RouteCollection;
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

    /**
     * @var \Symfony\Component\Routing\RouterInterface[] Array of routers, sorted by priority
     */
    protected $sortedRouters;

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
        $this->sortedRouters = array();
    }

    /**
     * Sorts the routers and flattens them.
     *
     * @return array
     */
    public function all()
    {
        if (empty($this->sortedRouters)) {
            $this->sortedRouters = $this->sortRouters();
        }

        return $this->sortedRouters;
    }

    /**
     * Sort routers by priority.
     * The highest priority number is the highest priority (reverse sorting)
     *
     * @return \Symfony\Component\Routing\RouterInterface[]
     */
    protected function sortRouters()
    {
        $sortedRouters = array();
        krsort($this->routers);

        foreach ($this->routers as $routers) {
            $sortedRouters = array_merge($sortedRouters, $routers);
        }

        return $sortedRouters;
    }

    /**
     * Loops through all routes and tries to match the passed url.
     *
     * @param string $url
     * @throws ResourceNotFoundException $e
     * @throws MethodNotAllowedException $e
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
                    $this->logger->addInfo('Router '.get_class($router).' was not able to match, message "'.$e->getMessage().'"');
                }
                // Needs special care
            } catch (MethodNotAllowedException $e) {
                if ($this->logger) {
                    $this->logger->addInfo('Router '.get_class($router).' throws MethodNotAllowedException with message "'.$e->getMessage().'"');
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
     * @param array $parameters
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
                    $this->logger->addInfo($e->getMessage());
                }
            }
        }

        throw new RouteNotFoundException(sprintf('None of the chained router was able to generate route "%s".', $name));
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
        $collection = new RouteCollection();
        foreach ($this->all() as $router) {
            $collection->addCollection($router->getRouteCollection());
        }
        return $collection;
    }
}
