<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Enhancer;

use Symfony\Component\HttpFoundation\Request;

/**
 * This enhancer uses a HttpFoundation request matcher to decide which enhancer
 * to use.
 *
 * @author David Buchmann
 */
class ConditionalEnhancer implements RouteEnhancerInterface
{
    /**
     * The enhancer of the first entry where the matcher matches the request is
     * used.
     *
     * The matcher has to implement Symfony\Component\HttpFoundation\RequestMatcherInterface.
     *
     * The enhancer has to implement RouteEnhancerInterface.
     *
     * @var array Ordered list of 'matcher', 'enhancer' pairs
     */
    private $enhancerMap;

    /**
     * @param array $enhancerMap Ordered list of 'matcher', 'enhancer' pairs
     */
    public function __construct(array $enhancerMap)
    {
        $this->enhancerMap = $enhancerMap;
    }

    /**
     * If the target field is not set but the source field is, map the field.
     *
     * {@inheritdoc}
     */
    public function enhance(array $defaults, Request $request)
    {
        foreach ($this->enhancerMap as $pair) {
            if ($pair['matcher']->matches($request)) {
                return $pair['enhancer']->enhance($defaults, $request);
            }
        }

        return $defaults;
    }
}
