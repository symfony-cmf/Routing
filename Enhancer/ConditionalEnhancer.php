<?php

namespace Symfony\Cmf\Component\Routing\Enhancer;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class ConditionalEnhancer implements RouteEnhancerInterface
{
    /**
     * @var RouteEnhancerInterface[][]
     */
    protected $enhancers = array();

    /**
     * Cached sorted list of enhancers.
     *
     * @var RouteEnhancerInterface[]
     */
    protected $sortedEnhancers = array();

    /**
     * The complete and available mapping separated by its name as the key.
     *
     * @var array
     */
    private $mapping;

    /**
     * @var Request
     */
    private $request;

    /**
     * Key that can should be used in a http method aware defaults configuration.
     */
    const KEY_METHODS = 'methods';
    const KEY_VALUE = 'value';

    /**
     * Matches all http methods.
     */
    const METHOD_ANY = 'any';

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(array $defaults, Request $request)
    {
        $this->request = $request;
        foreach ($this->getRouteEnhancers() as $enhancer) {
            $defaults = $enhancer->enhance($defaults, $this->request);
        }

        return $defaults;
    }

    /**
     * Add route enhancers to the router to let them generate information on
     * matched routes.
     *
     * The order of the enhancers is determined by the priority, the higher the
     * value, the earlier the enhancer is run.
     *
     * @param RouteEnhancerInterface $enhancer
     * @param int $priority
     *
     * @return $this
     */
    public function addRouteEnhancer(RouteEnhancerInterface $enhancer, $priority = 0)
    {
        if (empty($this->enhancers[$priority])) {
            $this->enhancers[$priority] = array();
        }

        $this->enhancers[$priority][] = $enhancer;
        $this->sortedEnhancers = array();

        return $this;
    }

    /**
     * Sorts the enhancers and flattens them.
     *
     * @return RouteEnhancerInterface[] the enhancers ordered by priority
     */
    public function getRouteEnhancers()
    {
        if (empty($this->sortedEnhancers)) {
            $this->sortedEnhancers = $this->sortAndWarmEnhancers();
        }

        return $this->sortedEnhancers;
    }

    /**
     * Sort enhancers by priority.
     *
     * The highest priority number is the highest priority (reverse sorting).
     *
     * @return RouteEnhancerInterface[] the sorted enhancers
     */
    protected function sortAndWarmEnhancers()
    {
        $sortedEnhancers = array();
        krsort($this->enhancers);

        $mapping = $this->mapping;
        foreach ($this->enhancers as $enhancers) {
            $map = array_map(
                function (RouteEnhancerInterface $enhancer) use ($mapping) {
                    foreach ($mapping as $name => $map) {
                        if ($enhancer instanceof WithMapping && $enhancer->isName($name)) {
                            $enhancer->setMapping($this->transformMethodAwareMapping($map));

                            return $enhancer;
                        }
                    }

                    return $enhancer instanceof WithMapping ? null : $enhancer;
                },
                $enhancers
            );
            $sortedEnhancers = array_merge($sortedEnhancers, array_filter($map, function ($enhancer) {
                return null !== $enhancer;
            }));
        }
        foreach ($this->mapping as $name => $mapping) {

        }

        return $sortedEnhancers;
    }

    /**
     * {@inheritdoc}
     */
    public function isName($name)
    {
        return false;
    }

    private function transformMethodAwareMapping($mappings)
    {
        $transformed = array();
        foreach ($mappings as $mapping) {
            if (!is_array($mapping) || !isset($mapping[self::KEY_METHODS])|| !isset($mapping[self::KEY_VALUE])) {
                $transformed[] = $mapping;
                continue;
            }

            if (is_array($mapping[self::KEY_METHODS])
                && (in_array(strtolower($this->request->getMethod()), $mapping[self::KEY_METHODS])
                    || in_array(self::METHOD_ANY, $mapping[self::KEY_METHODS]))
            ) {
                $transformed[] = $mapping[self::KEY_VALUE];
            }
        }

        return $transformed;
    }
}
