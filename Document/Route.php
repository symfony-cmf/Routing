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
     * Variable pattern part. The static part of the pattern is the id without the prefix.
     * @PHPCRODM\String
     */
    protected $variablePattern;

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
        $this->setDefaults(array());
        $this->setRequirements(array());
        $this->setOptions(array());
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
    public function getStaticPrefix()
    {
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

    /**
     * {@inheritDoc}
     */
    public function getPattern()
    {
        return $this->getStaticPrefix() . $this->getVariablePattern();
    }

    /**
     * {@inheritDoc}
     *
     * It is recommended to use setVariablePattern to just set the part after
     * the fixed part that follows from the repository path. If you use this
     * method, it will ensure the start of the pattern matches the repository
     * path (id) of this route document. Make sure to persist the route before
     * setting the pattern to have the id field initialized.
     */
    public function setPattern($pattern)
    {
        $len = strlen($this->getStaticPrefix());
        if (strncmp($this->getStaticPrefix(), $pattern, $len)) {
            throw new \LogicException('You can not set the route document to a pattern that does not match its repository path. First move it to the correct path.');
        }
        return $this->setVariablePattern($pattern, $len);
    }

    /**
     * @return string the variable part of the url pattern
     */
    public function getVariablePattern()
    {
        return $this->variablePattern;
    }

    /**
     * @param string $variablePattern the variable part of the url pattern
     * @return Route
     */
    public function setVariablePattern($variablePattern)
    {
        $this->variablePattern = $variablePattern;
        // calling parent mainly to let it set compiled=null. the parent $pattern field is never used
        return parent::setPattern($this->getStaticPrefix() . $this->variablePattern);
    }

    // workaround for the missing hashmaps in phpcr-odm

    /**
     * @PHPCRODM\PostLoad
     */
    public function initArrays()
    {
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
