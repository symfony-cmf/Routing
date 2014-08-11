<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class LazyRouteCollection extends RouteCollection
{
    /**
     * The route provider for this generator.
     *
     * @var RangedRouteProviderInterface
     */
    protected $provider;

    /**
     * Contains the amount of route which are loaded on each provider request.
     */
    const ROUTE_LOADED_PER_TIME = 50;

    /**
     * Stores the current loaded routes.
     *
     * @var \Symfony\Component\Routing\Route[]
     */
    protected $elements;

    /**
     * Contains the current item the iterator points to.
     *
     * @var int
     */
    protected $currentRoute = 0;

    public function __construct(RangedRouteProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Gets the current RouteCollection as an Iterator that includes all routes.
     *
     * It implements \IteratorAggregate.
     *
     * @see all()
     *
     * @return \ArrayIterator An \ArrayIterator object for iterating over routes
     */
    public function getIterator()
    {
      return new \ArrayIterator($this);
    }

    /**
     * Gets the number of Routes in this collection.
     *
     * @return int The number of routes
     */
    public function count()
    {
        return count($this->all());
    }

    /**
     * Returns all routes in this collection.
     *
     * @return Route[] An array of routes
     */
    public function all()
    {
        return $this->provider->getRoutesByNames(null);
    }

    /**
     * Gets a route by name.
     *
     * @param string $name The route name
     *
     * @return Route|null A Route instance or null when not found
     */
    public function get($name)
    {
        try {
            return $this->provider->getRouteByName($name);
        } catch (RouteNotFoundException $e) {
            return null;
        }
    }

    /**
     * Loads the next routes into the elements array.
     *
     * @param int $offset
     *   The offset used in the db query.
     */
    protected function loadNextElements($offset)
    {
      $this->elements = array();

      $this->elements = $this->provider->getRoutesRanged($offset, static::ROUTE_LOADED_PER_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function current() {
      return current($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function next() {
      $result = next($this->elements);
      if ($result === FALSE) {
        $this->loadNextElements($this->currentRoute + 1);
      }
      $this->currentRoute++;
    }

    /**
     * {@inheritdoc}
     */
    public function key() {
      return key($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function valid() {
      return key($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind() {
      $this->currentRoute = 0;
      $this->loadNextElements($this->currentRoute);
    }

}
