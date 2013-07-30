<?php

namespace Symfony\Cmf\Component\Routing\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class RouterMatchEvent extends Event
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @return Request | null
     */
    public function getRequest()
    {
        return $this->request;
    }
}
