<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Controller\ControllerResolver;

/**
 * A router that reads entries from a Object-Document Mapper store.
 *
 * @author Philippo de Santis
 * @author David Buchmann
 */
class ContentRouter implements RouterInterface
{
    protected $om;
    protected $resolver;
    protected $context;

    public function __construct(ObjectManager $om, ControllerResolver $resolver)
    {
        $this->setObjectManager($om);
        $this->setControllerResolver($resolver);
    }

    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException If there is no such route in the database
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        /* TODO */
        throw new \Symfony\Component\Routing\Exception\RouteNotFoundException;
    }
    public function getRouteCollection()
    {
        /* TODO */
        return new \Symfony\Component\Routing\RouteCollection();
    }


    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function setControllerResolver(ControllerResolver $resolver)
    {
        // TODO: allow more than 1 resolver
        $this->resolver = $resolver;
    }

    /**
     * Returns an array of parameter like this
     *
     * array(
     *   "_controller" => "NameSpace\Controller::action",
     *   "reference" => $document,
     * )
     *
     * @throws ResourceNotFoundException If the requested url does not exist in the ODM
     * @throws MethodNotAllowedException If the resource was found but the request method is not allowed
     *
     * @param string $url the full requested url. TODO: what about language in url and things?
     *
     * @return array as described above
     */
    public function match($url)
    {
        // TODO: optionally inject the document/entity class name so this can work with ORM too
        $document = $this->om->find(null, $url);

        if (!$document instanceof RouteObjectInterface) {
            throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException("No entry or not a route at '$url'");
        }

        $defaults = $this->resolver->getController($document);
        if (empty($defaults['_controller'])) {
            throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException("The resolver was not able to determine a controller for '$url'");;
        }

        $defaults['reference'] = $document->getReference();

        return $defaults;
    }

}
