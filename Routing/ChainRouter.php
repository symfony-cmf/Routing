<?php
/*
Copyright (C) 2011 by E-butik i Norden AB

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

namespace Symfony\Bundle\ChainRoutingBundle\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\Exception\NotFoundException;

class ChainRouter implements RouterInterface
{
    private $subrouters = array();
    private $priority_list = array();

    public function setSubRouter(array $subRouters)
    {
        foreach ($subRouters as $priority => $subRouter) {
            $this->addSubRouter($subRouter, $priority);
        }
    }

    /**
     * Adds a subrouter to the chain router, at a given priority. A higher priority number means
     * higher priority.
     *
     * @author Magnus Nordlander <magnus@e-butik.se>
     *
     * @param  Symfony\Component\Routing\RouterInterface $subrouter The subrouter to be added
     * @param  integer $priority The priority of the subrouter. Higher number means higher priority. Optional.
     */
    public function addSubRouter(RouterInterface $subrouter, $priority = 0)
    {
        if (!isset($this->priority_list[$priority]) || !is_array($this->priority_list[$priority])) {
            $this->priority_list[$priority] = array($subrouter);
        } else {
            $this->priority_list[$priority][] = $subrouter;
        }

        $this->rebuildSubRouters();
    }

    /**
     * Rebuilds the internal prioritized array of subrouters.
     *
     * @author Magnus Nordlander <magnus@e-butik.se>
     */
    private function rebuildSubRouters()
    {
        $this->subrouters = array();

        krsort($this->priority_list, SORT_NUMERIC);

        foreach ($this->priority_list as $priority_subrouter_array) {
            foreach ($priority_subrouter_array as $subrouter) {
              $this->subrouters[] = $subrouter;
            }
        }
    }

    /**
     * Tries to match a URL with a set of routes.
     *
     * Returns false if no route matches the URL.
     *
     * @author Magnus Nordlander <magnus@e-butik.se>
     *
     * @param  string $url URL to be parsed
     *
     * @return array An array of parameters
     * @throws Symfony\Component\Routing\Matcher\Exception\NotFoundException
     */
    public function match($url)
    {
        foreach ($this->subrouters as $subrouter) {
            try {
                $match = $subrouter->match($url);
                return $match;
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        throw new NotFoundException();
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @author Magnus Nordlander <magnus@e-butik.se>
     *
     * @param  string  $name       The name of the route
     * @param  array   $parameters An array of parameters
     * @param  Boolean $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     * @throws InvalidArgumentException
     */
    public function generate($name, array $parameters = array(), $absolute = false)
    {
        foreach ($this->subrouters as $subrouter) {
            try {
                $url = $subrouter->generate($name, $parameters, $absolute);
                return $url;
            } catch (\InvalidArgumentException $e) {
                // Do nothing
            }
        }

        throw new \InvalidArgumentException(sprintf('Route "%s" does not exist.', $name));
    }

    /**
     * Sets the request context.
     *
     * @author Magnus Nordlander <magnus@e-butik.se>
     *
     * @param Symfony\Component\Routing\RequestContext $context  The context
     */
    public function setContext(RequestContext $context)
    {
        foreach ($this->subrouters as $subrouter) {
            if (method_exists($subrouter, 'setContext')) {
                $subrouter->setContext($context);
            }
        }
    }
}
