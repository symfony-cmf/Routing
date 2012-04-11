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
 * If the route provides a content, that content is placed in the request
 * object with the CONTENT_KEY for the controller to use.
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
        } else {
            $route = $this->getRouteFromContent($parameters);
        }

        if (! $route instanceof RouteObjectInterface) {
            $hint = is_object($route) ? get_class($route) : gettype($route);
            throw new RouteNotFoundException('Route of this document is not instance of RouteObjectInterface but: '.$hint);
        }

        $url = $this->context->getBaseUrl() . $route->getUrl();

        // add a query string if needed
        $variables = array(
            'route' => null,
            'content' => null,
            '_locale' => null
        ); // FIXME: hack to not get these as query parameters

        // TODO: this is copy-pasted from symfony UrlGenerator
        $extra = array_diff_key($parameters, $variables);
        if ($extra && $query = http_build_query($extra)) {
            $url .= '?'.$query;
        }

        // TODO: this is copy-pasted from symfony UrlGenerator
        // we should try to somehow reuse the code there rather than copy-paste
        if ($absolute) {
            $scheme = $this->context->getScheme();
            $port = '';
            if ('http' === $scheme && 80 != $this->context->getHttpPort()) {
                $port = ':'.$this->context->getHttpPort();
            } elseif ('https' === $scheme && 443 != $this->context->getHttpsPort()) {
                $port = ':'.$this->context->getHttpsPort();
            }

            $url = $scheme.'://'.$this->context->getHost().$port.$url;
        }

        return $url;
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
        $route = $this->routeRepository->findByUrl($url);

        if (! $route instanceof RouteObjectInterface) {
            throw new ResourceNotFoundException("No entry or not a route at '$url'");
        }

        $defaults = $route->getDefaults();
//var_dump($route->getRouteContent());
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
        $defaults['_route'] = 'chain_router_doctrine_route'.str_replace('/', '_', $url);

        return $defaults;
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
            throw new RouteNotFoundException;
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
