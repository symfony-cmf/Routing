<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\Routing\Event;

final class Events
{
    /**
     * Fired before a path is matched in \Symfony\Cmf\Component\Routing\DynamicRouter#match
     *
     * The event object is RouteMatchEvent.
     */
    const PRE_DYNAMIC_MATCH = 'cmf_routing.pre_dynamic_match';

    /**
     * Fired before a Request is matched in \Symfony\Cmf\Component\Routing\DynamicRouter#match
     *
     * The event object is RouteMatchEvent.
     */
    const PRE_DYNAMIC_MATCH_REQUEST = 'cmf_routing.pre_dynamic_match_request';
}
