<?php

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;

use Symfony\Cmf\Component\Routing\Mapper\ControllerMapperInterface;

/**
 * A router that reads route entries from a repository. The repository can
 * easily be implemented using an object-document mapper like Doctrine
 * PHPCR-ODM but you are free to use something different.
 *
 * This router is based on the symfony routing matcher and generator. Different
 * to the default router, the route collection is loaded from the injected
 * route repository custom per request to not load a potentially large number
 * of routes that are known to not match anyways.
 *
 * If the route provides a content, that content is placed in the defaults
 * returned by the match() method in field RouteObjectInterface::CONTENT_OBJECT.
 *
 * @author Philippo de Santis
 * @author David Buchmann
 * @author Uwe Jäger
 */
class DynamicRouter implements RouterInterface, ChainedRouterInterface
{
    /**
     * Symfony routes always need a name in the collection. We generate routes
     * based on the route object, but need to use a name for example in error
     * reporting.
     * When generating, we just use this prefix, when matching, we append
     * whatever the repository returned as ID, replacing anything but
     * [^a-z0-9A-Z_.] with "_" to get unique valid route names.
     */
    const ROUTE_GENERATE_DUMMY_NAME = 'cmf_routing_dynamic_route';

    /**
     * @var array of ControllerMapperInterface
     */
    protected $mappers = array();
    /**
     * The route repository to get routes from
     *
     * @var RouteRepositoryInterface
     */
    protected $routeRepository;

    /**
     * The content repository used to find content by it's id
     * This can be used to specify a parameter content_id when generating urls
     *
     * This is optional and might not be initialized.
     *
     * @var  ContentRepositoryInterface
     */
    protected $contentRepository;

    /**
     * Context to get the base url from.
     *
     * @var RequestContext
     */
    protected $context;

    /**
     * @param RouteRepositoryInterface $routeRepository The repository to get routes from
     */
    public function __construct(RouteRepositoryInterface $routeRepository)
    {
        $this->routeRepository = $routeRepository;
    }

    /**
     * Set an optional content repository to find content by ids
     *
     * @param ContentRepositoryInterface $contentRepository
     */
    public function setContentRepository(ContentRepositoryInterface $contentRepository)
    {
        $this->contentRepository = $contentRepository;
    }

    /**
     * Add as many mappers as you want, they are asked for the controller in
     * the order they are added here.
     *
     * @param ControllerMapperInterface $mapper a helper to map the request to
     *      controller name
     */
    public function addControllerMapper(ControllerMapperInterface $mapper)
    {
        $this->mappers[] = $mapper;
    }

    /**
     * {@inheritDoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $name ignored
     * @param array $parameters must either contain the field 'route' with a
     *      RouteObjectInterface or the field 'content' with the document
     *      instance to get the route for (implementing RouteAwareInterface)
     *
     * @throws RouteNotFoundException If there is no such route in the database
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        if (isset($parameters['route']) && '' !== $parameters['route']) {
            $route = $parameters['route'];
            unset($parameters['route']);
        } elseif (is_string($name) && $name) {
            $route = $this->routeRepository->getRouteByName($name, $parameters);
        } else {
            $route = $this->getRouteFromContent($name, $parameters);
            unset($parameters['route']); // could be an empty string
        }

        if (! $route instanceof RouteObjectInterface) {
            $hint = is_object($route) ? get_class($route) : gettype($route);
            throw new RouteNotFoundException('Route of this document is not an instance of RouteObjectInterface but: '.$hint);
        }

        $collection = new RouteCollection();
        $collection->add(self::ROUTE_GENERATE_DUMMY_NAME, $route);

        return $this->getGenerator($collection)->generate(self::ROUTE_GENERATE_DUMMY_NAME, $parameters, $absolute);
    }

    /**
     * Get an url matcher for this collection
     *
     * @param RouteCollection $collection collection of routes for the current request
     *
     * @return UrlGeneratorInterface the url matcher instance
     */
    public function getGenerator(RouteCollection $collection)
    {
        // TODO: option to configure class?
        return new UrlGenerator($collection, $this->context);
    }

    public function getRouteCollection()
    {
        /* TODO */
        return new RouteCollection();
    }

    /**
     * Returns an array of parameter like this
     *
     * array(
     *   RouteObjectInterface::CONTROLLER_NAME => "NameSpace\Controller::indexAction",
     *   RouteObjectInterface::CONTENT_OBJECT => $document,
     * )
     *
     * The controller can be either the fully qualified class name or the
     * service name of a controller that is registered as a service. In both
     * cases, the action to call on that controller is appended, separated with
     * two colons.
     *
     * @param string $url the full requested url.
     *
     * @return array as described above
     *
     * @throws ResourceNotFoundException If the requested url does not exist in the ODM
     * @throws \Symfony\Component\Routing\Exception\MethodNotAllowedException
     *      If the resource was found but the request method is not allowed
     */
    public function match($url)
    {
        $routes = $this->routeRepository->findManyByUrl($url);

        $defaults = $this->getMatcher($routes)->match($url);
        $route = $routes->get($defaults['_route']);

        if (empty($defaults[RouteObjectInterface::CONTROLLER_NAME])) {
            // if content does not provide explicit controller, try to find it with one of the mappers
            $controller = false;
            foreach ($this->mappers as $mapper) {
                $controller = $mapper->getController($route, $defaults);
                if ($controller !== false) {
                    break;
                }
            }

            if (false === $controller) {
                throw new ResourceNotFoundException("The mapper was not able to determine a controller for '$url'");;
            }

            $defaults[RouteObjectInterface::CONTROLLER_NAME] = $controller;
        }

        if ($route instanceof RouteObjectInterface && $content = $route->getRouteContent()) {
            $defaults[RouteObjectInterface::CONTENT_OBJECT] = $content;
        }

        return $defaults;
    }

    /**
     * Get an url matcher for this collection
     *
     * @param RouteCollection $collection collection of routes for the current request
     *
     * @return \Symfony\Component\Routing\Matcher\UrlMatcherInterface the url matcher instance
     */
    public function getMatcher(RouteCollection $collection)
    {
        // TODO: option to configure class?
        return new UrlMatcher($collection, $this->context);
    }

    public function supports($name)
    {
        return !$name || is_string($name) || $name instanceof RouteAwareInterface || $name instanceof RouteObjectInterface;
    }

    /**
     * Get the route based on the content field in parameters
     *
     * Called in generate when there is no route given in the parameters.
     *
     * If there is more than one route for the content, tries to find the
     * first one that matches the _locale (provided in $parameters or otherwise
     * defaulting to the request locale).
     *
     * If none is found, falls back to just return the first route.
     *
     * @param mixed $name
     * @param array $parameters which should contain a content field containing a RouteAwareInterface object
     *
     * @return Route the route instance
     *
     * @throws RouteNotFoundException if there is no content field in the
     *      parameters or its not possible to build a route from that object
     */
    protected function getRouteFromContent($name, &$parameters)
    {
        if ($name instanceof RouteAwareInterface) {
            $content = $name;
        } elseif (isset($parameters['content_id']) && null !== $this->contentRepository) {
            $content = $this->contentRepository->findById($parameters['content_id']);
        } elseif (isset($parameters['content'])) {
            $content = $parameters['content'];
        }

        unset($parameters['content'], $parameters['content_id']);

        if (empty($content)) {
            throw new RouteNotFoundException('Neither the route name, nor a parameter "content" or "content_id" could be resolved to an content instance');
        }

        if (!$content instanceof RouteAwareInterface) {
            $hint = is_object($content) ? get_class($content) : gettype($content);
            throw new RouteNotFoundException('The content does not implement RouteAwareInterface: ' . $hint);
        }

        $routes = $content->getRoutes();
        if (empty($routes)) {
            $hint = property_exists($content, 'path') ? $content->path : get_class($content);
            throw new RouteNotFoundException('Document has no route: ' . $hint);
        }

        $locale = $this->getLocale($parameters);

        if (isset($locale)) {
            foreach ($routes as $route) {
                if (! $route instanceof SymfonyRoute) {
                    continue;
                }
                $defaults = $route->getDefaults();
                if (isset($defaults['_locale']) && $locale == $defaults['_locale']) {
                    return $route;
                }
            }
        }
        // if none matched, continue and randomly return the first one

        return reset($routes);
    }

    /**
     * Determine the locale to be used with this request
     *
     * @param array $parameters the parameters determined by the route
     *
     * @return string|null the locale following of the parameters or any other
     *  information the router has available.
     */
    protected function getLocale($parameters)
    {
        if (isset($parameters['_locale'])) {
            return $parameters['_locale'];
        }

        return null;
    }
}
