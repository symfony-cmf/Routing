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
    class ChainRouterGenerateBcLayer
    {
        /**
         * Loops through all registered routers and returns a router if one is found.
         * It will always return the first route generated.
         */
        public function generate(string $name, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
        {
            $debug = [];

            foreach ($this->all() as $router) {
                // if $router does not announce it is capable of handling
                // non-string routes and $name is not a string, continue
                if (array_key_exists('_cmf_route', $parameters) && is_object($parameters['_cmf_route']) && !$router instanceof VersatileGeneratorInterface) {
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
} else {
    /**
     * @internal
     */
    class ChainRouterGenerateBcLayer
    {
        /**
         * Loops through all registered routers and returns a router if one is found.
         * It will always return the first route generated.
         */
        public function generate($name, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
        {
            $debug = [];

            if ($name && !is_string($name)) {
                @trigger_error('Passing an object as the route name is deprecated in symfony-cmf/Routing v2.2 and will not work in Symfony 5.0. Pass an empty route name and the object as "_cmf_route" parameter in the parameters array.', E_USER_DEPRECATED);
            }

            foreach ($this->all() as $router) {
                // if $router does not announce it is capable of handling
                // non-string routes and $name is not a string, continue
                if ((($name && !is_string($name)) || (array_key_exists('_cmf_route', $parameters) && is_object($parameters['_cmf_route']))) && !$router instanceof VersatileGeneratorInterface) {
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
}
