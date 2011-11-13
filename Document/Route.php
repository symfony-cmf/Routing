<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;

/**
 * Default document for routing table entries that work with the DoctrineRouter.
 *
 * @author david.buchmann@liip.ch
 *
 * @PHPCRODM\Document(alias="route")
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
    private $reference;

    /**
     * Controller alias for rendering the target content, to be used with the
     * ControllerAliasResolver.
     *
     * @PHPCRODM\String()
     */
    protected $controller_alias;

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
    public function setReference($document)
    {
        $this->reference = $document;
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
    public function getReference()
    {
        return $this->reference;
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
     * To work with the ControllerAliasResolver, this must at least contain
     * the field 'type' with a value from the controllers_by_alias mapping
     *
     * @return array Information for the ControllerResolver
     */
    public function getRouteDefaults()
    {
        $alias = $this->getControllerAlias();
        if (! empty($alias)) {
            return array('type' => $alias);
        }
        return array();
    }
}
