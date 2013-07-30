<?php

namespace Symfony\Cmf\Component\Routing\Event;

final class Events
{
    /**
     * Fired before a path is matched in \Symfony\Cmf\Component\Routing\DynamicRouter#match
     */
    const PRE_DYNAMIC_MATCH = 'cmf_routing.pre_dynamic_match';

    /**
     * Fired before a Request is matched in \Symfony\Cmf\Component\Routing\DynamicRouter#match
     */
    const PRE_DYNAMIC_MATCH_REQUEST = 'cmf_routing.pre_dynamic_match_request';
}
