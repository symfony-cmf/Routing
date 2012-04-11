<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Document;

use Symfony\Component\Routing\Route as SymfonyRoute;
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
class Route extends SymfonyRoute implements RouteObjectInterface
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
     * The part of the phpcr path that is not part of the url
     * @var string
     */
    protected $idPrefix;

    /**
     * @PHPCRODM\String
     */
    protected $patternOdm;

    /**
     * @var \Doctrine\ODM\PHPCR\MultivaluePropertyCollection
     * @PHPCRODM\String(multivalue=true)
     */
    protected $defaultsKeys;
    /**
     * @var \Doctrine\ODM\PHPCR\MultivaluePropertyCollection
     * @PHPCRODM\String(multivalue=true)
     */
    protected $defaultsValues;
    /**
     * @var \Doctrine\ODM\PHPCR\MultivaluePropertyCollection
     * @PHPCRODM\String(multivalue=true)
     */
    protected $requirementsKeys;
    /**
     * @var \Doctrine\ODM\PHPCR\MultivaluePropertyCollection
     * @PHPCRODM\String(multivalue=true)
     */
    protected $requirementsValues;
    /**
     * @var \Doctrine\ODM\PHPCR\MultivaluePropertyCollection
     * @PHPCRODM\String(multivalue=true)
     */
    protected $optionsKeys;
    /**
     * @var \Doctrine\ODM\PHPCR\MultivaluePropertyCollection
     * @PHPCRODM\String(multivalue=true)
     */
    protected $optionsValues;

    /**
     * Overwrite to be able to create route without pattern
     */
    public function __construct()
    {
        parent::__construct('');
    }

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
     * {@inheritDoc}
     */
    public function getUrl()
    {
        // TODO: this is basically the "pattern". we should set the pattern automatically if not explicitly set and remove this method
        if (strncmp($this->getPath(), $this->idPrefix, strlen($this->idPrefix))) {
            throw new \LogicException("The id prefix '".$this->idPrefix."' does not match the route document path '".$this->getPath()."'");
        }
        $url = substr($this->getPath(), strlen($this->idPrefix));
        if (empty($url)) {
            $url = '/';
        }
        return $url;
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
    public function getRouteContent()
    {
        return $this->routeContent;
    }

    // workaround for the missing hashmaps in phpcr-odm

    /**
     * @PHPCRODM\PostLoad
     */
    public function initArrays()
    {
        $this->setPattern($this->patternOdm);
        // phpcr-odm makes this a property collection. for some reason
        // array_combine does not work with ArrayAccess objects
        // if there are no values in a multivalue property, we don't get an
        // empty collection assigned but null

        if ($this->defaultsValues && count($this->defaultsValues)) {
            $this->setDefaults(array_combine(
                $this->defaultsKeys->getValues(),
                $this->defaultsValues->getValues())
            );
        } else {
            $this->setDefaults(array());
        }
        if ($this->requirementsValues && count($this->requirementsValues)) {
            $this->setRequirements(array_combine(
                $this->requirementsKeys->getValues(),
                $this->requirementsValues->getValues())
            );
        } else {
            $this->setRequirements(array());
        }
        if ($this->optionsValues && count($this->optionsValues)) {
            $this->setOptions(array_combine(
                $this->optionsKeys->getValues(),
                $this->optionsValues->getValues())
            );
        } else {
            $this->setOptions(array());
        }
    }

    /**
     * @PHPCRODM\PreUpdate
     * @PHPCRODM\PrePersist
     */
    public function prepareArrays()
    {
        $this->patternOdm = $this->getPattern();

        $defaults = $this->getDefaults();
        $this->defaultsKeys = array_keys($defaults);
        $this->defaultsValues = array_values($defaults);

        $requirements = $this->getRequirements();
        $this->requirementsKeys = array_keys($requirements);
        $this->requirementsValues = array_values($requirements);

        $options = $this->getOptions();
        $this->optionsKeys = array_keys($options);
        $this->optionsValues = array_values($options);
    }
}
