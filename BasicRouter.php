<?php

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class BasicRouter implements RouterInterface, RequestMatcherInterface
{

    protected $matcher;

    protected $generator;

    protected $context;

    /**
     * Constructs a new BasicRotuer.
     *
     * @param RequestContext $context
     * @param RequestMatcherInterface|UrlMatcherInterface $matcher
     * @param UrlGeneratorInterface $generator
     */
    public function __construct(RequestContext $context, $matcher, UrlGeneratorInterface $generator)
    {
        $this->context = $context;
        $this->matcher = $matcher;
        $this->generator = $generator;

        $this->generator->setContext($context);
    }

    /**
     * Not implemented.
     */
    public function getRouteCollection() {
      return new RouteCollection();
    }

    /**
     * Tries to match a URL path with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the exceptions documented
     * below.
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
        if (! $this->matcher instanceof UrlMatcherInterface) {
            throw new \Exception('Wrong matcher type');
        }
        return $this->matcher->match($pathinfo);
    }

    /**
     * Tries to match a request with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the exceptions documented
     * below.
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
        if (! $this->matcher instanceof UrlMatcherInterface) {
            return $this->matcher->match($request->getPathInfo());
        }

        return $this->matcher->matchRequest($request);
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
      return $this->generator->generate($name, $parameters, $absolute);
    }

    /**
     * Sets the request context.
     *
     * @param RequestContext $context The context
     *
     * @api
     */
    public function setContext(RequestContext $context) {
      $this->context = $context;
      $this->generator->setContext($context);
    }

    /**
     * Gets the request context.
     *
     * @return RequestContext The context
     *
     * @api
     */
    public function getContext() {
      return $this->context;
    }
}
