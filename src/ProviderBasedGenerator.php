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
use Symfony\Component\Routing\Exception\RouteNotFoundException;
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
    /**
     * The route provider for this generator.
     *
     * @var RouteProviderInterface
     */
    protected $provider;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(RouteProviderInterface $provider, LoggerInterface $logger = null)
    {
        $this->provider = $provider;
        $this->logger = $logger;
        $this->context = new RequestContext();
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH): string
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('The "$name" parameter should of type string.');
        }

        if (RouteObjectInterface::OBJECT_BASED_ROUTE_NAME === $name
            && array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters)
            && $parameters[RouteObjectInterface::ROUTE_OBJECT] instanceof SymfonyRoute
        ) {
            $route = $parameters[RouteObjectInterface::ROUTE_OBJECT];
            unset($parameters[RouteObjectInterface::ROUTE_OBJECT]);
        } elseif (null === $route = $this->provider->getRouteByName($name)) {
            throw new RouteNotFoundException(sprintf('Route "%s" does not exist.', $name));
        }

        // the Route has a cache of its own and is not recompiled as long as it does not get modified
        $compiledRoute = $route->compile();
        $hostTokens = $compiledRoute->getHostTokens();

        $debug_message = $this->getRouteDebugMessage($name);

        return $this->doGenerate($compiledRoute->getVariables(), $route->getDefaults(), $route->getRequirements(), $compiledRoute->getTokens(), $parameters, $debug_message, $referenceType, $hostTokens);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteDebugMessage($name, array $parameters = [])
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

        if (is_scalar($name)) {
            return $name;
        }

        // legacy
        if (is_array($name)) {
            return serialize($name);
        }

        if ($name instanceof RouteObjectInterface) {
            return 'Route with key '.$name->getRouteKey();
        }

        if ($name instanceof SymfonyRoute) {
            return 'Route with path '.$name->getPath();
        }

        if (is_object($name)) {
            return get_class($name);
        }

        return 'Null route';
    }
}
