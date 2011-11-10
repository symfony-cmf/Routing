<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Doctrine\Common\Persistence\ObjectManager;
// TODO: Interface this!
use Symfony\Cmf\Bundle\ChainRoutingBundle\Controller\ControllerResolver;

/**
 * A router that reads entries from a Object-Document Mapper store.
 *
 * For Doctrine PHPCR-ODM, inject the $idPrefix to point to the node under
 * which you stored the route documents.
 *
 * For other doctrine types, inject $routeClass so that this router knows in
 * which table to look for routes. It will call find on the object manager with
 * this class and the url. Make sure to provide a repository implementation
 * that can find the document/entity by url.
 *
 * @author Philippo de Santis
 * @author David Buchmann
 */
class DoctrineRouter implements RouterInterface
{
    protected $om;
    protected $resolver;
    protected $routeClass;
    protected $idPrefix;
    protected $context;

    /**
     * @param ObjectManager $om The doctrine entity resp. document manager
     * @param ControllerResolver $resolver The helper to resolve controller
     *      responsible for the content found at the matched url
     * @param string $routeClass Class name to pass to $om->find for
     *      repositories that require the class of the Entity/Document to find.
     *      Automatically detected on phpcr-odm.
     * @param string $idPrefix A prefix to prepend to the url when looking it
     *      up in the repository, used with phpcr-odm to specify the node
     *      containing the route nodes. This must start with / and may not end
     *      with / as the url passed in will start with /.
     */
    public function __construct(ObjectManager $om, ControllerResolver $resolver, $routeClass = null, $idPrefix = '')
    {
        $this->setObjectManager($om);
        $this->setControllerResolver($resolver);
        $this->routeClass = $routeClass;
        $this->idPrefix = $idPrefix;
    }

    // inherit doc
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }
    // inherit doc
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     *
     * @throws RouteNotFoundException If there is no such route in the database
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        /* TODO */
        // we have to use the $this->idPrefix here too i guess
        throw new \Symfony\Component\Routing\Exception\RouteNotFoundException;
    }
    public function getRouteCollection()
    {
        /* TODO */
        return new \Symfony\Component\Routing\RouteCollection();
    }

    /**
     * Set the doctrine entity or document manager that will know the urls
     */
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
        $document = $this->om->find($this->routeClass, $this->idPrefix . $url);

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
