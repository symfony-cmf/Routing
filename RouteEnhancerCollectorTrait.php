<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;

/**
 * A trait for allowing any service to collect and apply route enhancers.
 */
trait RouteEnhancerCollectorTrait {

  /**
   * @var RouteEnhancerInterface[]
   */
  protected $enhancers = array();

  /**
   * Cached sorted list of enhancers
   *
   * @var RouteEnhancerInterface[]
   */
  protected $sortedEnhancers = array();

  /**
   * Apply the route enhancers to the defaults, according to priorities
   *
   * @param array   $defaults
   * @param Request $request
   *
   * @return array
   */
  protected function applyRouteEnhancers($defaults, Request $request)
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
   * @param int                    $priority
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
      $this->sortedEnhancers = $this->sortRouteEnhancers();
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
  protected function sortRouteEnhancers()
  {
    $sortedEnhancers = array();
    krsort($this->enhancers);

    foreach ($this->enhancers as $enhancers) {
      $sortedEnhancers = array_merge($sortedEnhancers, $enhancers);
    }

    return $sortedEnhancers;
  }

}
