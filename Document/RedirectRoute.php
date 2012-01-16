<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RedirectRouteInterface;

/**
 * Document for redirection entries with the RedirectController.
 *
 * This document may have (in order of precedence):
 *
 * - uri: an absolute uri
 * - routeName and routeParameters: to be used with a routers generate method
 *
 * With standard Symfony routing, you can just use routeName and a hashmap of
 * parameters. For the doctrine router, you need to set the routeTarget but can
 * omit the routeName.
 *
 *
 * @author David Buchmann <david@liip.ch>
 *
 * @PHPCRODM\Document
 */
class RedirectRoute implements RedirectRouteInterface
{
    /**
     * @PHPCRODM\ParentDocument
     */
    protected $parent;
    /**
     * @PHPCRODM\Nodename
     */
    protected $name;

    /**
     * The full repository path to this route object
     * TODO: the strategy=parent argument should not be needed, we do have a ParentDocument annotation
     * @PHPCRODM\Id(strategy="parent")
     */
    protected $path;

    /**
     * Absolute uri to redirect to
     * @PHPCRODM\Uri
     */
    protected $uri;

    /**
     * The name of the target route (for use with standard symfony routes)
     * @PHPCRODM\String
     */
    protected $routeName;

    /**
     * @PHPCRODM\ReferenceOne
     */
    protected $routeTarget;

    /**
     * Simulate a php hashmap in phpcr. This holds the keys
     *
     * @PHPCRODM\String(multivalue=true)
     */
    protected $parameterKeys;

    /**
     * Simulate a php hashmap in phpcr. This holds the keys
     *
     * @PHPCRODM\String(multivalue=true)
     */
    protected $parameter;

    /**
     * Set the parent document and name of this route entry. Only allowed when
     * creating a new item!
     *
     * The url will be the url of the parent plus the supplied name.
     */
    public function setPosition($parent, $name)
    {
      $this->parent = $parent;
      $this->name = $name;
    }
    /**
     * Get the path of this url entry
     */
    public function getPath()
    {
      return $this->path;
    }

    /**
     * There are no defaults here. We set up a map from class to controller
     *
     * @return array empty array
     */
    public function getRouteDefaults()
    {
        return array();
    }

    /**
     * This route returns itself as content, as the RedirectController needs
     * the route object to build the RedirectResponse
     *
     * @return self
     */
    public function getRouteContent()
    {
        return $this;
    }

    /**
     * Set the document this url points to
     */
    public function setRouteTarget(RouteObjectInterface $document)
    {
        $this->routeTarget = $document;
    }
    /**
     * Get the content document this route entry stands for. If non-null,
     * the ControllerClassResolver uses it to identify a controller and
     * the content is passed to the controller.
     *
     * If there is no specific content for this url (i.e. its an "application"
     * page), may return null.
     *
     * @return object the document or entity this route entry points to
     */
    public function getRouteTarget()
    {
        return $this->routeTarget;
    }

    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param array $parameter a hashmap of key to value mapping for route
     *      parameters
     */
    public function setParameters(array $parameter)
    {
        $this->parameter = $parameter;
        $this->parameterKeys = array_keys($parameter);
    }

    /**
     * Merge the given string parameters and the targetRoute document
     *
     * @return array Information to build the route
     */
    public function getParameters()
    {
        $parameters = array();

        if ($this->parameterKeys !== null) {
            $i = 0;
            foreach($this->parameterKeys as $key) {
                $parameters[$key] = $this->parameter[$i];
            }
        }

        $route = $this->getRouteTarget();
        if (! empty($route)) {
            $parameters['route'] = $route;
        }

        return $parameters;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getUri()
    {
        return $this->uri;
    }
}
