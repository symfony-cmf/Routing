<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;

/**
 * Default document for routing table entries that work with the DoctrineRouter.
 *
 * This needs the IdPrefix service to run and setPrefix whenever a route is
 * loaded. Otherwise the getUrl method will be invalid.
 *
 * @author david.buchmann@liip.ch
 *
 * @PHPCRODM\Document(referenceable=true,repositoryClass="Symfony\Cmf\Bundle\ChainRoutingBundle\Document\RouteRepository")
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
    protected $routeContent;

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
     * Locale to use when this route is requested.
     * Only set in getRouteDefaults if non-empty
     *
     * @PHPCRODM\String()
     */
    protected $locale;

    /**
     * The part of the phpcr path that is not part of the url
     * @var string
     */
    protected $idPrefix;

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
     * Get the repository path of this url entry
     */
    public function getPath()
    {
      return $this->path;
    }
    public function setPrefix($idPrefix)
    {
        $this->idPrefix = $idPrefix;
    }

    /**
     * Set the document this url points to
     */
    public function setRouteContent($document)
    {
        $this->routeContent = $document;
    }
    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        $url = substr($this->getPath(), strlen($this->idPrefix));
        if (empty($url)) {
            $url = '/';
        }
        return $url;
    }

    /**
     * {@inheritDoc}
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
     * Set the locale requests for this route should use
     *
     * @param $locale the locale of this route
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string the locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritDoc}
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
        $locale = $this->getLocale();;
        if (! empty($locale)) {
            $defaults['_locale'] = $locale;
        }
        return $defaults;
    }
}
