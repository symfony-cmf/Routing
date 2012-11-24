<?php

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

use Symfony\Cmf\Component\Routing\Mapper\RouteEnhancerInterface;

/**
 * A base router class for the cmf. Uses the RouteEnhancer concept to generate
 * data on routes.
 */
class DynamicRouter implements RouterInterface, RequestMatcherInterface, ChainedRouterInterface
{
    /**
     * @var RequestMatcherInterface|UrlMatcherInterface
     */
    protected $matcher;

    /**
     * @var UrlGeneratorInterface
     */
    protected $generator;

    /**
     * @var RouteEnhancerInterface[]
     */
    protected $enhancers = array();

    /**
     * @var RequestContext
     */
    protected $context;

    /**
     * @param RequestContext                              $context
     * @param RequestMatcherInterface|UrlMatcherInterface $matcher
     * @param UrlGeneratorInterface                       $generator
     */
    public function __construct(RequestContext $context, $matcher, UrlGeneratorInterface $generator)
    {
        $this->context = $context;
        if (! $matcher instanceof RequestMatcherInterface && ! $matcher instanceof UrlMatcherInterface) {
            throw new \InvalidArgumentException('Invalid $matcher');
        }
        $this->matcher = $matcher;
        $this->generator = $generator;

        $this->generator->setContext($context);
    }

    /**
     * Not implemented.
     */
    public function getRouteCollection()
    {
        return new RouteCollection();
    }

    /**
     * @return RequestMatcherInterface|UrlMatcherInterface
     */
    public function getMatcher()
    {
        // we may not set the context in DynamicRouter::setContext as this would lead to symfony cache warmup problems
        $this->matcher->setContext($this->getContext());

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
     * Tries to match a URL path with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the
     * exceptions documented below.
     *
     * @param string $pathinfo The path info to be parsed (raw format, i.e. not urldecoded)
     *
     * @return array An array of parameters
     *
     * @throws ResourceNotFoundException If the resource could not be found
     * @throws MethodNotAllowedException If the resource was found but the request method is not allowed
     *
     * @api
     */
    public function match($pathinfo)
    {
        $matcher = $this->getMatcher();
        if (! $matcher instanceof UrlMatcherInterface) {
            throw new \Exception('Wrong matcher type');
        }

        return $matcher->match($pathinfo);
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
     * @throws MethodNotAllowedException If a matching resource was found but the request method is not allowed
     */
    public function matchRequest(Request $request)
    {
        $matcher = $this->getMatcher();
        if ($matcher instanceof UrlMatcherInterface) {
            return $matcher->match($request->getPathInfo());
        }

        $defaults = $matcher->matchRequest($request);

        foreach ($this->enhancers as $enhancer) {
            $defaults = $enhancer->enhance($defaults, $request);
        }

        return $defaults;

    }

    /**
     * Generates a URL from the given parameters.
     *
     * If the generator is not able to generate the url, it must throw the RouteNotFoundException
     * as documented below.
     *
     * @param string  $name       The name of the route
     * @param mixed   $parameters An array of parameters
     * @param Boolean $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     *
     * @throws RouteNotFoundException if route doesn't exist
     *
     * @api
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        $generator = $this->getGenerator();

        return $generator->generate($name, $parameters, $absolute);
    }

    /**
     * Add route enhancers to the router to let them generate information on matched routes.
     *
     * The order of the enhancers is determined by the order they are added to the router.
     *
     * @param RouteEnhancerInterface $enhancer
     */
    public function addRouteEnhancer(RouteEnhancerInterface $enhancer)
    {
        $this->enhancers[] = $enhancer;
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
     * Support any string as route name
     *
     * {@inheritDoc}
     */
    public function supports($name)
    {
        return is_string($name);
    }
}
