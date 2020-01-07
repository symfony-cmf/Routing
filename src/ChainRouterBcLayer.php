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

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

$refl = new \ReflectionClass(UrlGeneratorInterface::class);
$generateMethod = $refl->getMethod('generate');
$methodParameters = $generateMethod->getParameters();
/** @var \ReflectionParameter $nameParameter */
$nameParameter = array_shift($methodParameters);
if ($nameParameter && $nameParameter->hasType() && 'string' === $nameParameter->getType()) {
    /**
     * @internal
     */
    class ChainRouterBcLayer extends ChainRouterBaseBcLayer
    {
        /**
         * Loops through all registered routers and returns a router if one is found.
         * It will always return the first route generated.
         */
        public function generate(string $name, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
        {
            return $this->doGenerate($name, $parameters, $absolute);
        }
    }
} else {
    /**
     * @internal
     */
    class ChainRouterBcLayer extends ChainRouterBaseBcLayer
    {
        /**
         * Loops through all registered routers and returns a router if one is found.
         * It will always return the first route generated.
         */
        public function generate($name, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
        {
            if (!is_string($name)) {
                @trigger_error(sprintf('Passing an object as the route name is deprecated in symfony-cmf/Routing v2.2 and will not work in Symfony 5.0. Pass an empty route name and the object as "%s" parameter in the parameters array.', RouteObjectInterface::ROUTE_OBJECT), E_USER_DEPRECATED);

                $parameters[RouteObjectInterface::ROUTE_OBJECT] = $name;
                $name = '';
            }

            return $this->doGenerate($name, $parameters, $absolute);
        }
    }
}

/**
 * @internal
 */
abstract class ChainRouterBaseBcLayer
{
    protected function doGenerate($name, $parameters, $absolute)
    {
        $debug = [];

        foreach ($this->all() as $router) {
            // if $router does not announce it is capable of handling
            // non-string routes and $name is not a string, continue
            if (array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters) && is_object($parameters[RouteObjectInterface::ROUTE_OBJECT]) && !$router instanceof VersatileGeneratorInterface) {
                continue;
            }

            $routeName = $name;
            if (array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters) && is_object($parameters[RouteObjectInterface::ROUTE_OBJECT])) {
                $routeName = $parameters[RouteObjectInterface::ROUTE_OBJECT];
            }

            // If $router is versatile and doesn't support this route name, continue
            if ($router instanceof VersatileGeneratorInterface && !$router->supports($routeName)) {
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
}
