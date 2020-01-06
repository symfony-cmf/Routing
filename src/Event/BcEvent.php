<?php

namespace Symfony\Cmf\Component\Routing\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event as ContractsEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

// Clean up when sf 3.4 support is removed
if (is_subclass_of(EventDispatcherInterface::class, ContractsEventDispatcherInterface::class)) {
    /**
     * @internal
     */
    abstract class BcEvent extends ContractsEvent
    {
    }
} else {
    /**
     * @internal
     */
    abstract class BcEvent extends Event
    {
    }
}
