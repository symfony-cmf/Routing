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
use Psr\Log\NullLogger;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as SymfonyRoute;

/**
 * A Generator that uses a RouteProvider rather than a RouteCollection.
 *
 * @author Larry Garfield
 */
class ProviderBasedGenerator extends UrlGenerator implements VersatileGeneratorInterface
{
    protected RouteProviderInterface $provider;

    public function __construct(RouteProviderInterface $provider, LoggerInterface $logger = null)
    {
        $this->provider = $provider;
        $this->logger = $logger ?: new NullLogger();
        $this->context = new RequestContext();
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        if (RouteObjectInterface::OBJECT_BASED_ROUTE_NAME === $name
            && array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters)
            && $parameters[RouteObjectInterface::ROUTE_OBJECT] instanceof SymfonyRoute
        ) {
            $route = $parameters[RouteObjectInterface::ROUTE_OBJECT];
            unset($parameters[RouteObjectInterface::ROUTE_OBJECT]);
        } else {
            $route = $this->provider->getRouteByName($name);
        }

        // the Route has a cache of its own and is not recompiled as long as it does not get modified
        $compiledRoute = $route->compile();
        $hostTokens = $compiledRoute->getHostTokens();

        $debug_message = $this->getRouteDebugMessage($name);

        return $this->doGenerate($compiledRoute->getVariables(), $route->getDefaults(), $route->getRequirements(), $compiledRoute->getTokens(), $parameters, $debug_message, $referenceType, $hostTokens);
    }

    public function getRouteDebugMessage(string $name, array $parameters = []): string
    {
        if (RouteObjectInterface::OBJECT_BASED_ROUTE_NAME === $name
            && array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters)
        ) {
            $routeObject = $parameters[RouteObjectInterface::ROUTE_OBJECT];
            if ($routeObject instanceof RouteObjectInterface) {
                return 'Route with key '.$routeObject->getRouteKey();
            }

            if ($routeObject instanceof SymfonyRoute) {
                return 'Route with path '.$routeObject->getPath();
            }

            if (is_object($routeObject)) {
                return get_class($routeObject);
            }

            return 'Null route';
        }

        return $name;
    }
}
