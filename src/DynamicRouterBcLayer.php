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

$urlGeneratorParameters = (new \ReflectionClass(UrlGeneratorInterface::class))->getMethod('generate')->getParameters();
$urlGeneratorNameParameter = array_shift($urlGeneratorParameters);
if ($urlGeneratorNameParameter && $urlGeneratorNameParameter->hasType() && 'string' === $urlGeneratorNameParameter->getType()) {
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
                @trigger_error(sprintf('Passing an object as the route name is deprecated in symfony-cmf/Routing v2.2 and will not work in Symfony 5.0. Pass the `RouteObjectInterface::OBJECT_BASED_ROUTE_NAME` constant as the route name and the object as "%s" parameter in the parameters array.', RouteObjectInterface::ROUTE_OBJECT), E_USER_DEPRECATED);
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
            if (RouteObjectInterface::OBJECT_BASED_ROUTE_NAME === $name && array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters) && is_object($parameters[RouteObjectInterface::ROUTE_OBJECT])) {
                $routeParam = $parameters[RouteObjectInterface::ROUTE_OBJECT];
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
