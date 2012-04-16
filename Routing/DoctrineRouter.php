<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver\ControllerResolverInterface;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * A router that reads route entries from an Object-Document Mapper store.
 *
 * This is basically using the symfony routing matcher and generator. Different
 * to the default router, the route collection is loaded from the injected
 * route repository custom per request to not load a potentially large number
 * of routes that are known to not match anyways.
 *
 * If the route provides a content, that content is placed in the request
 * object with the CONTENT_KEY for the controller to use.
 *
 * @author Philippo de Santis
 * @author David Buchmann
 */
class DoctrineRouter implements RouterInterface
{
    /**
     * key for the request attribute that contains the content document if this
     * route has one associated
     */
    const CONTENT_KEY = 'contentDocument';

    /**
     * key for the request attribute that contains the template this document
     * wants to use
     */
    const CONTENT_TEMPLATE = 'contentTemplate';

    /**
     * @var array of ContentResolverInterface
     */
    protected $resolvers;
    /**
     * The route repository to get routes from
     * @var RouteRepositoryInterface
     */
    protected $routeRepository;

    /**
     * To get the request from, as its not available immediatly
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Context to get the base url from.
     *
     * @var RequestContext
     */
    protected $context;

    /**
     * @param ContainerInterface $container the dependency injection container
     *      to get the request object to place the content in it, if the
     *      matched route provides a content document.
     * @param RouteRepositoryInterface $routeRepository The repository to get routes from
     */
    public function __construct(ContainerInterface $container, RouteRepositoryInterface $routeRepository)
    {
        $this->container = $container;
        $this->routeRepository = $routeRepository;
    }

    /**
     * Add as many resolvers as you want, they are asked for the controller in
     * the order they are added here.
     *
     * @param ControllerResolverInterface $resolver a helper to resolve the
     *      controller responsible for the matched url
     */
    public function addControllerResolver(ControllerResolverInterface $resolver)
    {
        $this->resolvers[] = $resolver;
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
        } else {
            $route = $this->getRouteFromContent($parameters);
            unset($parameters['content']);
            unset($parameters['route']); // could be an empty string
        }

        if (! $route instanceof RouteObjectInterface) {
            $hint = is_object($route) ? get_class($route) : gettype($route);
            throw new RouteNotFoundException('Route of this document is not an instance of RouteObjectInterface but: '.$hint);
        }

        $collection = new RouteCollection();
        $collection->add('x', $route);

        return $this->getGenerator($collection)->generate('x', $parameters, $absolute);
    }

    /**
     * Get an url matcher for this collection
     *
     * @param RouteCollection $collection collection of routes for the current request
     *
     * @return UrlMatcherInterface the url matcher instance
     */
    public function getGenerator(RouteCollection $collection)
    {
        // TODO: option to configure class?
        return new \Symfony\Component\Routing\Generator\UrlGenerator($collection, $this->context);
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
     *   "_controller" => "NameSpace\Controller::indexAction",
     *   "reference" => $document,
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
     * @throws MethodNotAllowedException If the resource was found but the request method is not allowed
     */
    public function match($url)
    {
        $routes = $this->routeRepository->findManyByUrl($url);

        $collection = new RouteCollection();

        foreach ($routes as $key => $route) {
            $collection->add('chain_router_doctrine_route'.str_replace('/', '_', $key), $route);
        }

        $defaults = $this->getMatcher($collection)->match($url);
        $route = $collection->get($defaults['_route']);

        if (empty($defaults['_controller'])) {
            // if content does not provide explicit controller, try to find it with one of the resolvers
            foreach ($this->resolvers as $resolver) {
                $controller = $resolver->getController($route, $defaults);
                if ($controller !== false) break;
            }
            if (false === $controller) {
                throw new ResourceNotFoundException("The resolver was not able to determine a controller for '$url'");;
            }
            $defaults['_controller'] = $controller;
        }

        if ($content = $route->getRouteContent()) {
            if (! $request = $this->container->get('request')) {
                throw new \Exception('Request object not available from container');
            }

            $request->attributes->set(self::CONTENT_KEY, $content);
        }
        $defaults['path'] = $url; // TODO: get rid of this

        return $defaults;
    }

    /**
     * Get an url matcher for this collection
     *
     * @param RouteCollection $collection collection of routes for the current request
     *
     * @return UrlMatcherInterface the url matcher instance
     */
    public function getMatcher(RouteCollection $collection)
    {
        // TODO: option to configure class?
        return new \Symfony\Component\Routing\Matcher\UrlMatcher($collection, $this->context);
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
     * @param array $parameters which should contain a content field containing a RouteAwareInterface object
     *
     * @return the route instance
     *
     * @throws RouteNotFoundException if there is no content field in the
     *      parameters or its not possible to build a route from that object
     */
    protected function getRouteFromContent($parameters)
    {
        if (! isset($parameters['content'])) {
            throw new RouteNotFoundException('No parameter "content" and neither "route"');
        }

        if (! $parameters['content'] instanceof RouteAwareInterface) {
            $hint = is_object($parameters['content']) ? get_class($parameters['content']) : gettype($parameters['content']);
            throw new RouteNotFoundException('The content does not implement RouteAwareInterface: ' . $hint);
        }

        $routes = $parameters['content']->getRoutes();
        if (empty($routes)) {
            $hint = property_exists($parameters['content'], 'path') ? $parameters['content']->path : get_class($parameters['content']);
            throw new RouteNotFoundException('Document has no route: ' . $hint);
        }

        if (isset($parameters['_locale'])) {
            $locale =  $parameters['_locale'];
        } else {
            if (! $request = $this->container->get('request')) {
                throw new \Exception('Request object not available from container');
            }
            $locale = $request->getLocale();
        }

        foreach ($routes as $route) {
            if (! $route instanceof RouteObjectInterface) continue;
            $defaults = $route->getDefaults();
            if (isset($defaults['_locale']) && $locale == $defaults['_locale']) {
                return $route;
            }
        }
        // if none matched, continue and randomly return the first one

        return reset($routes);
    }
}
