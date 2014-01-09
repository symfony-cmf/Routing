<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\Routing\Event;

use Symfony\Component\HttpFoundation\Request;

class RouterPostMatchEvent extends RouterMatchEvent
{
    /**
     * @var array
     */
    protected $defaults;

    /**
     * @param array $defaults
     * @param Request $request
     */
    public function __construct(array $defaults, Request $request = null)
    {
        parent::__construct($request);
        $this->defaults = $defaults;
    }

    /**
     * @return array
     */
    public function &getDefaults() {
        return $this->defaults;
    }
}
