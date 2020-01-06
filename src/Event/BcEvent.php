<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
