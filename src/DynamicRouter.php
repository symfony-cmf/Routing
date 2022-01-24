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
use Symfony\Component\Routing\RouteCollection;

/**
 * A flexible router accepting matcher and generator through injection and
 * using the RouteEnhancer concept to generate additional data on the routes.
 *
 * @author Larry Garfield
 * @author David Buchmann
 */
class DynamicRouter implements RequestMatcherInterface, ChainedRouterInterface
{
    use RouteEnhancerTrait;

    private RequestMatcherInterface|UrlMatcherInterface $matcher;
    private UrlGeneratorInterface $generator;
    private ?EventDispatcherInterface $eventDispatcher;

    /**
     * The regexp pattern that needs to be matched before a dynamic lookup is
     * made.
     */
    private string $uriFilterRegexp;

    private ?RouteProviderInterface $provider;
    private RequestContext $context;
    private ?RouteCollection $routeCollection = null;

    public function __construct(
        RequestContext $context,
        UrlMatcherInterface|RequestMatcherInterface $matcher,
        UrlGeneratorInterface $generator,
        string $uriFilterRegexp = '',
        ?EventDispatcherInterface $eventDispatcher = null,
        ?RouteProviderInterface $provider = null
    ) {
        $this->context = $context;
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
    public function getRouteCollection(): ?RouteCollection
    {
        if (!$this->routeCollection instanceof RouteCollection) {
            $this->routeCollection = $this->provider
                ? new LazyRouteCollection($this->provider) : new RouteCollection();
        }

        return $this->routeCollection;
    }

    public function getMatcher(): UrlMatcherInterface|RequestMatcherInterface
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

    public function getGenerator(): UrlGeneratorInterface
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
     * The CMF routing system used to allow to pass route objects as $name to generate the route.
     * To generate the route from a string, pass the RouteObjectInterface::OBJECT_BASED_ROUTE_NAME
     * as route name and the object in the parameters with key RouteObjectInterface::ROUTE_OBJECT.
     *
     * @throws RouteNotFoundException if route doesn't exist
     */
    public function generate(string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        if ($this->eventDispatcher) {
            $event = new RouterGenerateEvent($name, $parameters, $referenceType);
            $this->eventDispatcher->dispatch($event, Events::PRE_DYNAMIC_GENERATE);
            $name = $event->getRoute();
            $parameters = $event->getParameters();
            $referenceType = $event->getReferenceType();
        }

        return $this->getGenerator()->generate($name, $parameters, $referenceType);
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
     * @api
     */
    public function match(string $pathinfo): array
    {
        $request = Request::create($pathinfo);
        if ($this->eventDispatcher) {
            $event = new RouterMatchEvent();
            $this->eventDispatcher->dispatch($event, Events::PRE_DYNAMIC_MATCH);
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
    public function matchRequest(Request $request): array
    {
        if ($this->eventDispatcher) {
            $event = new RouterMatchEvent($request);
            $this->eventDispatcher->dispatch($event, Events::PRE_DYNAMIC_MATCH_REQUEST);
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

    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    public function getContext(): RequestContext
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     *
     * Forwards to the generator.
     */
    public function getRouteDebugMessage(string $name, array $parameters = []): string
    {
        if ($this->generator instanceof VersatileGeneratorInterface) {
            return $this->generator->getRouteDebugMessage($name, $parameters);
        }

        return "Route '$name' not found";
    }
}
