<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * The ChainRouter allows to combine several routers to try in a defined order.
 *
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 * @author Magnus Nordlander <magnus@e-butik.se>
 */
class ChainRouter implements ChainRouterInterface, WarmableInterface
{
    /**
     * @var RequestContext|null
     */
    private $context;

    /**
     * Array of arrays of routers grouped by priority.
     *
     * @var RouterInterface[][] Priority => RouterInterface[]
     */
    private $routers = [];

    /**
     * @var RouterInterface[] List of routers, sorted by priority
     */
    private $sortedRouters = [];

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @return RequestContext
     */
    public function getContext()
    {
        if (!$this->context) {
            $this->context = new RequestContext();
        }

        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function add($router, $priority = 0)
    {
        if (!$router instanceof RouterInterface
            && !($router instanceof RequestMatcherInterface && $router instanceof UrlGeneratorInterface)
        ) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid router.', get_class($router)));
        }
        if (empty($this->routers[$priority])) {
            $this->routers[$priority] = [];
        }

        $this->routers[$priority][] = $router;
        $this->sortedRouters = [];
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        if (0 === count($this->sortedRouters)) {
            $this->sortedRouters = $this->sortRouters();

            // setContext() is done here instead of in add() to avoid fatal errors when clearing and warming up caches
            // See https://github.com/symfony-cmf/Routing/pull/18
            if (null !== $this->context) {
                foreach ($this->sortedRouters as $router) {
                    if ($router instanceof RequestContextAwareInterface) {
                        $router->setContext($this->context);
                    }
                }
            }
        }

        return $this->sortedRouters;
    }

    /**
     * Sort routers by priority.
     * The highest priority number is the highest priority (reverse sorting).
     *
     * @return RouterInterface[]
     */
    protected function sortRouters()
    {
        if (0 === count($this->routers)) {
            return [];
        }

        krsort($this->routers);

        return call_user_func_array('array_merge', $this->routers);
    }

    /**
     * {@inheritdoc}
     *
     * Loops through all routes and tries to match the passed url.
     *
     * Note: You should use matchRequest if you can.
     */
    public function match($pathinfo)
    {
        return $this->doMatch($pathinfo);
    }

    /**
     * {@inheritdoc}
     *
     * Loops through all routes and tries to match the passed request.
     */
    public function matchRequest(Request $request)
    {
        return $this->doMatch($request->getPathInfo(), $request);
    }

    /**
     * Loops through all routers and tries to match the passed request or url.
     *
     * At least the  url must be provided, if a request is additionally provided
     * the request takes precedence.
     *
     * @param string  $pathinfo
     * @param Request $request
     *
     * @return array An array of parameters
     *
     * @throws ResourceNotFoundException If no router matched
     */
    private function doMatch($pathinfo, Request $request = null)
    {
        $methodNotAllowed = null;

        $requestForMatching = $request;
        foreach ($this->all() as $router) {
            try {
                // the request/url match logic is the same as in Symfony/Component/HttpKernel/EventListener/RouterListener.php
                // matching requests is more powerful than matching URLs only, so try that first
                if ($router instanceof RequestMatcherInterface) {
                    if (null === $requestForMatching) {
                        $requestForMatching = $this->rebuildRequest($pathinfo);
                    }

                    return $router->matchRequest($requestForMatching);
                }

                // every router implements the match method
                return $router->match($pathinfo);
            } catch (ResourceNotFoundException $e) {
                if ($this->logger) {
                    $this->logger->debug('Router '.get_class($router).' was not able to match, message "'.$e->getMessage().'"');
                }
                // Needs special care
            } catch (MethodNotAllowedException $e) {
                if ($this->logger) {
                    $this->logger->debug('Router '.get_class($router).' throws MethodNotAllowedException with message "'.$e->getMessage().'"');
                }
                $methodNotAllowed = $e;
            }
        }

        $info = $request
            ? "this request\n$request"
            : "url '$pathinfo'";

        throw $methodNotAllowed ?: new ResourceNotFoundException("None of the routers in the chain matched $info");
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $name
     *
     * The CMF routing system used to allow to pass route objects as $name to generate the route.
     * Since Symfony 5.0, the UrlGeneratorInterface declares $name as string. We widen the contract
     * for BC but deprecate passing non-strings.
     * Instead, Pass the RouteObjectInterface::OBJECT_BASED_ROUTE_NAME as route name and the object
     * in the parameters with key RouteObjectInterface::ROUTE_OBJECT.
     *
     * Loops through all registered routers and returns a router if one is found.
     * It will always return the first route generated.
     */
    public function generate($name, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if (is_object($name)) {
            @trigger_error('Passing an object as route name is deprecated since version 2.3. Pass the `RouteObjectInterface::OBJECT_BASED_ROUTE_NAME` as route name and the object in the parameters with key `RouteObjectInterface::ROUTE_OBJECT`.', E_USER_DEPRECATED);
        }

        $debug = [];

        foreach ($this->all() as $router) {
            // if $router does not announce it is capable of handling
            // non-string routes and $name is not a string, continue
            if ($name && !is_string($name) && !$router instanceof VersatileGeneratorInterface) {
                continue;
            }

            // If $router is versatile and doesn't support this route name, continue
            if ($router instanceof VersatileGeneratorInterface && !$router->supports($name)) {
                continue;
            }

            try {
                return $router->generate($name, $parameters, $absolute);
            } catch (RouteNotFoundException $e) {
                $hint = $this->getErrorMessage($name, $router, $parameters);
                $debug[] = $hint;
                if ($this->logger) {
                    $this->logger->debug('Router '.get_class($router)." was unable to generate route. Reason: '$hint': ".$e->getMessage());
                }
            }
        }

        if ($debug) {
            $debug = array_unique($debug);
            $info = implode(', ', $debug);
        } else {
            $info = $this->getErrorMessage($name);
        }

        throw new RouteNotFoundException(sprintf('None of the chained routers were able to generate route: %s', $info));
    }

    /**
     * Rebuild the request object from a URL with the help of the RequestContext.
     *
     * If the request context is not set, this returns the request object built from $pathinfo.
     *
     * @param string $pathinfo
     *
     * @return Request
     */
    private function rebuildRequest($pathinfo)
    {
        $context = $this->getContext();

        $uri = $pathinfo;

        $server = [];
        if ($context->getBaseUrl()) {
            $uri = $context->getBaseUrl().$pathinfo;
            $server['SCRIPT_FILENAME'] = $context->getBaseUrl();
            $server['PHP_SELF'] = $context->getBaseUrl();
        }
        $host = $context->getHost() ?: 'localhost';
        if ('https' === $context->getScheme() && 443 !== $context->getHttpsPort()) {
            $host .= ':'.$context->getHttpsPort();
        }
        if ('http' === $context->getScheme() && 80 !== $context->getHttpPort()) {
            $host .= ':'.$context->getHttpPort();
        }
        $uri = $context->getScheme().'://'.$host.$uri.'?'.$context->getQueryString();

        return Request::create($uri, $context->getMethod(), $context->getParameters(), [], [], $server);
    }

    private function getErrorMessage($name, $router = null, $parameters = null)
    {
        if ($router instanceof VersatileGeneratorInterface) {
            // the $parameters are not forced to be array, but versatile generator does typehint it
            if (!is_array($parameters)) {
                $parameters = [];
            }
            $displayName = $router->getRouteDebugMessage($name, $parameters);
        } elseif (is_object($name)) {
            $displayName = method_exists($name, '__toString')
                ? (string) $name
                : get_class($name)
            ;
        } else {
            $displayName = (string) $name;
        }

        return "Route '$displayName' not found";
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        foreach ($this->all() as $router) {
            if ($router instanceof RequestContextAwareInterface) {
                $router->setContext($context);
            }
        }

        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     *
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

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        if (!$this->routeCollection instanceof RouteCollection) {
            $this->routeCollection = new ChainRouteCollection();
            foreach ($this->all() as $router) {
                $this->routeCollection->addCollection($router->getRouteCollection());
            }
        }

        return $this->routeCollection;
    }

    /**
     * Identify if any routers have been added into the chain yet.
     *
     * @return bool
     */
    public function hasRouters()
    {
        return 0 < count($this->routers);
    }
}
