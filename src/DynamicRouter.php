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

use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerTrait;
use Symfony\Cmf\Component\Routing\Event\Events;
use Symfony\Cmf\Component\Routing\Event\RouterGenerateEvent;
use Symfony\Cmf\Component\Routing\Event\RouterMatchEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * A flexible router accepting matcher and generator through injection and
 * using the RouteEnhancer concept to generate additional data on the routes.
 *
 * @author Larry Garfield
 * @author David Buchmann
 */
class DynamicRouter implements RouterInterface, RequestMatcherInterface, ChainedRouterInterface
{
    use RouteEnhancerTrait;

    /**
     * @var RequestMatcherInterface|UrlMatcherInterface
     */
    protected $matcher;

    /**
     * @var UrlGeneratorInterface
     */
    protected $generator;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * The regexp pattern that needs to be matched before a dynamic lookup is
     * made.
     *
     * @var string
     */
    protected $uriFilterRegexp;

    /**
     * @var RouteProviderInterface
     */
    private $provider;

    /**
     * @var RequestContext
     */
    protected $context;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @param RequestContext                              $context
     * @param RequestMatcherInterface|UrlMatcherInterface $matcher
     * @param UrlGeneratorInterface                       $generator
     * @param string                                      $uriFilterRegexp
     * @param EventDispatcherInterface|null               $eventDispatcher
     * @param RouteProviderInterface                      $provider
     *
     * @throws \InvalidArgumentException If the matcher is not a request or url matcher
     */
    public function __construct(RequestContext $context,
                                $matcher,
                                UrlGeneratorInterface $generator,
                                $uriFilterRegexp = '',
                                EventDispatcherInterface $eventDispatcher = null,
                                RouteProviderInterface $provider = null
    ) {
        $this->context = $context;
        if (!$matcher instanceof RequestMatcherInterface && !$matcher instanceof UrlMatcherInterface) {
            throw new \InvalidArgumentException(
                sprintf('Matcher must implement either %s or %s', RequestMatcherInterface::class, UrlMatcherInterface::class)
            );
        }
        $this->matcher = $matcher;
        $this->generator = $generator;
        $this->eventDispatcher = $eventDispatcher;
        $this->uriFilterRegexp = $uriFilterRegexp;
        $this->provider = $provider;

        $this->generator->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        if (!$this->routeCollection instanceof RouteCollection) {
            $this->routeCollection = $this->provider
                ? new LazyRouteCollection($this->provider) : new RouteCollection();
        }

        return $this->routeCollection;
    }

    /**
     * @return RequestMatcherInterface|UrlMatcherInterface
     */
    public function getMatcher()
    {
        /* we may not set the context in DynamicRouter::setContext as this
         * would lead to symfony cache warmup problems.
         * a request matcher does not need the request context separately as it
         * can get it from the request.
         */
        if ($this->matcher instanceof RequestContextAwareInterface) {
            $this->matcher->setContext($this->getContext());
        }

        return $this->matcher;
    }

    /**
     * @return UrlGeneratorInterface
     */
    public function getGenerator()
    {
        $this->generator->setContext($this->getContext());

        return $this->generator;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * If the generator is not able to generate the url, it must throw the
     * RouteNotFoundException as documented below.
     *
     * @param string|Route $name          The name of the route or the Route instance
     * @param mixed        $parameters    An array of parameters
     * @param bool|string  $referenceType The type of reference to be generated (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @throws RouteNotFoundException if route doesn't exist
     *
     * @api
     */
    public function generate($name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if ($this->eventDispatcher) {
            $event = new RouterGenerateEvent($name, $parameters, $referenceType);
            $this->eventDispatcher->dispatch(Events::PRE_DYNAMIC_GENERATE, $event);
            $name = $event->getRoute();
            $parameters = $event->getParameters();
            $referenceType = $event->getReferenceType();
        }

        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

    /**
     * Delegate to our generator.
     *
     * {@inheritdoc}
     */
    public function supports($name)
    {
        if ($this->generator instanceof VersatileGeneratorInterface) {
            return $this->generator->supports($name);
        }

        return is_string($name);
    }

    /**
     * Tries to match a URL path with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the
     * exceptions documented below.
     *
     * @param string $pathinfo The path info to be parsed (raw format, i.e. not
     *                         urldecoded)
     *
     * @return array An array of parameters
     *
     * @throws ResourceNotFoundException If the resource could not be found
     * @throws MethodNotAllowedException If the resource was found but the
     *                                   request method is not allowed
     *
     * @deprecated Use matchRequest exclusively to avoid problems. This method will be removed in version 2.0
     *
     * @api
     */
    public function match($pathinfo)
    {
        @trigger_error(__METHOD__.'() is deprecated since version 1.3 and will be removed in 2.0. Use matchRequest() instead.', E_USER_DEPRECATED);

        $request = Request::create($pathinfo);
        if ($this->eventDispatcher) {
            $event = new RouterMatchEvent();
            $this->eventDispatcher->dispatch(Events::PRE_DYNAMIC_MATCH, $event);
        }

        if (!empty($this->uriFilterRegexp) && !preg_match($this->uriFilterRegexp, $pathinfo)) {
            throw new ResourceNotFoundException("$pathinfo does not match the '{$this->uriFilterRegexp}' pattern");
        }

        $matcher = $this->getMatcher();
        if (!$matcher instanceof UrlMatcherInterface) {
            throw new \InvalidArgumentException('Wrong matcher type, you need to call matchRequest');
        }

        $defaults = $matcher->match($pathinfo);

        return $this->applyRouteEnhancers($defaults, $request);
    }

    /**
     * Tries to match a request with a set of routes and returns the array of
     * information for that route.
     *
     * If the matcher can not find information, it must throw one of the
     * exceptions documented below.
     *
     * @param Request $request The request to match
     *
     * @return array An array of parameters
     *
     * @throws ResourceNotFoundException If no matching resource could be found
     * @throws MethodNotAllowedException If a matching resource was found but
     *                                   the request method is not allowed
     */
    public function matchRequest(Request $request)
    {
        if ($this->eventDispatcher) {
            $event = new RouterMatchEvent($request);
            $this->eventDispatcher->dispatch(Events::PRE_DYNAMIC_MATCH_REQUEST, $event);
        }

        if ($this->uriFilterRegexp
            && !preg_match($this->uriFilterRegexp, $request->getPathInfo())
        ) {
            throw new ResourceNotFoundException("{$request->getPathInfo()} does not match the '{$this->uriFilterRegexp}' pattern");
        }

        $matcher = $this->getMatcher();
        if ($matcher instanceof UrlMatcherInterface) {
            $defaults = $matcher->match($request->getPathInfo());
        } else {
            $defaults = $matcher->matchRequest($request);
        }

        return $this->applyRouteEnhancers($defaults, $request);
    }

    /**
     * Sets the request context.
     *
     * @param RequestContext $context The context
     *
     * @api
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * Gets the request context.
     *
     * @return RequestContext The context
     *
     * @api
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     *
     * Forwards to the generator.
     */
    public function getRouteDebugMessage($name, array $parameters = [])
    {
        if ($this->generator instanceof VersatileGeneratorInterface) {
            return $this->generator->getRouteDebugMessage($name, $parameters);
        }

        return "Route '$name' not found";
    }
}
