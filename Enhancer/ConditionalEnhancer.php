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

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(array $defaults, Request $request)
    {
        foreach ($this->getRouteEnhancers() as $enhancer) {
            $defaults = $enhancer->enhance($defaults, $request);
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

        foreach ($this->mapping as $name => $mapping) {
            foreach ($this->enhancers as $enhancers) {
                $sortedEnhancers = array_merge($sortedEnhancers, array_filter(
                    $enhancers,
                    function (RouteEnhancerInterface $enhancer) use ($name) {
                        return $enhancer->isName($name);
                    }
                ));
            }
        }

        return $sortedEnhancers;
    }

    /**
     * {@inheritdoc}
     */
    public function isName($name)
    {&
        return false;
    }
}
