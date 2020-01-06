<?php

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
if ($nameParameter && $nameParameter->hasType() && $nameParameter->getType() === 'string') {
    /**
     * @internal
     */
    class DynamicRouterGenerateBcLayer
    {
        public function generate(string $name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
        {
            if ($this->eventDispatcher) {
                $eventName = Events::PRE_DYNAMIC_GENERATE;
                $event = new RouterGenerateEvent($name, $parameters, $referenceType);

                // LegacyEventDispatcherProxy exists in Symfony >= 4.3
                if (class_exists(LegacyEventDispatcherProxy::class)) {
                    // New Symfony 4.3 EventDispatcher signature
                    $this->eventDispatcher->dispatch($event, $eventName);
                } else {
                    // Old EventDispatcher signature
                    $this->eventDispatcher->dispatch($eventName, $event);
                }

                $name = $event->getRoute();
                $parameters = $event->getParameters();
                $referenceType = $event->getReferenceType();
            }

            return $this->getGenerator()->generate($name, $parameters, $referenceType);
        }
    }
} else {
    /**
     * @internal
     */
    class DynamicRouterGenerateBcLayer
    {
        public function generate($name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
        {
            if ($this->eventDispatcher) {
                $eventName = Events::PRE_DYNAMIC_GENERATE;
                $event = new RouterGenerateEvent($name, $parameters, $referenceType);

                // LegacyEventDispatcherProxy exists in Symfony >= 4.3
                if (class_exists(LegacyEventDispatcherProxy::class)) {
                    // New Symfony 4.3 EventDispatcher signature
                    $this->eventDispatcher->dispatch($event, $eventName);
                } else {
                    // Old EventDispatcher signature
                    $this->eventDispatcher->dispatch($eventName, $event);
                }


                $name = $event->getRoute();
                $parameters = $event->getParameters();
                $referenceType = $event->getReferenceType();
            }

            return $this->getGenerator()->generate($name, $parameters, $referenceType);
        }
    }
}
