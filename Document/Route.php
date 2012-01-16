<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;

/**
 * Default document for routing table entries that work with the DoctrineRouter.
 *
 * @author david.buchmann@liip.ch
 *
 * @PHPCRODM\Document(referenceable=true)
 */
class Route implements RouteObjectInterface
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
     * The referenced document
     *
     * @PHPCRODM\ReferenceOne
     */
    private $routeContent;

    /**
     * Explicit controller to be used instead of one of the resolvers
     *
     * @PHPCRODM\String()
     */
    protected $controller;

    /**
     * Controller alias for rendering the target content, to be used with the
     * ControllerAliasResolver.
     *
     * @PHPCRODM\String()
     */
    protected $controller_alias;

    /**
     * Explicit template to be used with the default controller.
     *
     * @PHPCRODM\String()
     */
    protected $template;

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
     * Set the document this url points to
     */
    public function setRouteContent($document)
    {
        $this->routeContent = $document;
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
    public function getRouteContent()
    {
        return $this->routeContent;
    }

    /**
     * Set the explicit controller to be used with this route.
     * i.e. service_name:indexAction or MyBundle:Default:index
     *
     * @param string $controller the controller to be used with this route
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get the explicit controller to be used with this route
     *
     * @return string the controller name or service name with action
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set an alias name of the controller for this url, to be used with the
     * ControllerAliasResolver
     *
     * @param string $alias the alias name as in the controllers_by_alias mapping
     */
    public function setControllerAlias($alias)
    {
        $this->controller_alias = $alias;
    }
    /**
     * Get an alias name of the controller for this url.
     *
     * @return string $alias the alias name
     */
    public function getControllerAlias()
    {
        return $this->controller_alias;
    }

    /**
     * Set the template to be used with this route.
     * i.e. SymfonyCmfContentBundle:StaticContent:index.html.twig
     *
     * @param string $template the template to be used with this route
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Get the template to be used with this route.
     *
     * @return string the template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * To work with the ControllerAliasResolver, this must at least contain
     * the field 'type' with a value from the controllers_by_alias mapping
     *
     * @return array Information for the ControllerResolver
     */
    public function getRouteDefaults()
    {
        $defaults = array();

        $controller = $this->getController();
        if (! empty($controller)) {
            $defaults['_controller'] = $controller;
        }
        $alias = $this->getControllerAlias();
        if (! empty($alias)) {
            $defaults['type'] = $alias;
        }
        $template = $this->getTemplate();
        if (! empty($template)) {
            $defaults['template'] = $template;
        }
        return $defaults;
    }
}
