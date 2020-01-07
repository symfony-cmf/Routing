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

use Symfony\Cmf\Component\Routing\Event\Events;
use Symfony\Cmf\Component\Routing\Event\RouterGenerateEvent;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
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
    class DynamicRouterBcLayer extends DynamicRouterBaseBcLayer
    {
        public function generate(string $name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
        {
            return $this->doGenerate($name, $parameters, $referenceType);
        }
    }
} else {
    /**
     * @internal
     */
    class DynamicRouterBcLayer extends DynamicRouterBaseBcLayer
    {
        public function generate($name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
        {
            if (!is_string($name)) {
                @trigger_error('Passing an object as the route name is deprecated in symfony-cmf/Routing v2.2 and will not work in Symfony 5.0. Pass an empty route name and the object as "_cmf_route" parameter in the parameters array.', E_USER_DEPRECATED);

                $parameters['_cmf_route'] = $name;
                $name = '';
            }

            return $this->doGenerate($name, $parameters, $referenceType);
        }
    }
}

/**
 * @internal
 */
abstract class DynamicRouterBaseBcLayer
{
    protected function doGenerate($name, $parameters, $referenceType)
    {
        if ($this->eventDispatcher) {
            $routeParam = $name;
            if (array_key_exists('_cmf_route', $parameters) && is_object($parameters['_cmf_route'])) {
                $routeParam = $parameters['_cmf_route'];
            }

            $event = new RouterGenerateEvent($routeParam, $parameters, $referenceType);
            $this->doDispatch(Events::PRE_DYNAMIC_GENERATE, $event);

            $name = $event->getRoute();
            $parameters = $event->getParameters();
            $referenceType = $event->getReferenceType();
        }

        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

    protected function doDispatch($eventName, $event)
    {
        // LegacyEventDispatcherProxy exists in Symfony >= 4.3
        if (class_exists(LegacyEventDispatcherProxy::class)) {
            // New Symfony 4.3 EventDispatcher signature
            $this->eventDispatcher->dispatch($event, $eventName);
        } else {
            // Old EventDispatcher signature
            $this->eventDispatcher->dispatch($eventName, $event);
        }
    }
}
