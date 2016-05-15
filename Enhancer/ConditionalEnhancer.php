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
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * This enhancer uses a HttpFoundation request matcher to decide which enhancer
 * to use.
 *
 * @author David Buchmann
 */
class ConditionalEnhancer implements RouteEnhancerInterface
{
    const MAP_KEY_ENHANCER = 'enhancer';
    const MAP_KEY_MATCHER = 'matcher';

    /**
     * The enhancer of the first entry where the matcher matches the request is
     * used.
     *
     * The matcher has to implement Symfony\Component\HttpFoundation\RequestMatcherInterface.
     *
     * The enhancer has to implement RouteEnhancerInterface.
     *
     * @var array Ordered list of 'matcher', 'enhancer' pairs.
     */
    private $enhancerMap;

    /**
     * The sorted list of enhancers.
     *
     * @var []
     */
    private $sortedEnhancerMap = array();

    /**
     * @param array $enhancerMap Ordered list of 'matcher', 'enhancer' pairs.
     * @param int   $position    Position to add in the list.
     */
    public function __construct(array $enhancerMap = array(), $position = 0)
    {
        $this->enhancerMap[$position] = $enhancerMap;
    }

    /**
     * If the target field is not set but the source field is, map the field.
     *
     * {@inheritdoc}
     */
    public function enhance(array $defaults, Request $request)
    {
        foreach ($this->getEnhancerMap() as $pair) {
            if ($pair[self::MAP_KEY_MATCHER]->matches($request)) {
                return $pair[self::MAP_KEY_ENHANCER]->enhance($defaults, $request);
            }
        }

        return $defaults;
    }

    /**
     * Creates a sorted list of enhancer map pairs. It is ordered by the position, which should be the key.
     *
     * @return array
     */
    public function getEnhancerMap()
    {
        if (count($this->enhancerMap) === 0 || count($this->sortedEnhancerMap) > 0) {
            return $this->sortedEnhancerMap;
        }

        ksort($this->enhancerMap);
        $this->sortedEnhancerMap = array();

        foreach ($this->enhancerMap as $entries) {
            foreach ($entries as $entry) {
                $this->sortedEnhancerMap[] = $entry;
            }
        }

        return $this->sortedEnhancerMap;
    }

    /**
     * To create and add an enhancer map entry.
     *
     * @param string    $className FQCN of the enhancer.
     * @param string    $source    Source value for the enhancer
     * @param string    $target    Target value for the enhancer
     * @param []|string $map       Map values for the enhancer
     * @param []        $methods   List of methods the enhancer is restricted on.
     * @param int       $position  To order the enhancer calls
     */
    public function createAndAddMapEntry($className, $source, $target, $map, $methods = null, $position = 0)
    {
        if (!isset($this->enhancerMap[$position])) {
            $this->enhancerMap[$position] = array();
        }

        $this->enhancerMap[$position][] = self::createMapEntry($className, $source, $target, $map, $methods);
    }

    /**
     * To create an enhancer map entry.
     *
     * @param string    $className FQCN of the enhancer.
     * @param string    $source    Source value for the enhancer
     * @param string    $target    Target value for the enhancer
     * @param []|string $map       Map values for the enhancer
     * @param []        $methods   List of methods the enhancer is restricted on.
     *
     * @return array
     */
    public static function createMapEntry($className, $source, $target, $map, $methods = null)
    {
        return array(
            self::MAP_KEY_MATCHER => new RequestMatcher(null, null, $methods),
            self::MAP_KEY_ENHANCER => new $className($source, $target, $map),
        );
    }
}
