<?php

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Symfony\Cmf\Component\Routing\RouteProviderInterface;

/**
 * A Generator that uses a RouteProvider rather than a RouteCollection
 *
 * @author crell
 */
class ProviderBasedGenerator extends UrlGenerator
{

    /**
     * The route provider for this generator.
     *
     * @var Symfony\Cmf\Component\Routing\RouteProviderInterface
     */
    protected $provider;

    public function __construct(RouteProviderInterface $provider, RequestContext $context, LoggerInterface $logger = null)
    {
        $this->provider = $provider;
        $this->context = $context;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        if (null === $route = $this->provider->getRouteByName($name)) {
            throw new RouteNotFoundException(sprintf('Route "%s" does not exist.', $name));
        }

        // the Route has a cache of its own and is not recompiled as long as it does not get modified
        $compiledRoute = $route->compile();

        return $this->doGenerate($compiledRoute->getVariables(), $route->getDefaults(), $route->getRequirements(), $compiledRoute->getTokens(), $parameters, $name, $absolute, $compiledRoute->getHostnameTokens());
    }
}

