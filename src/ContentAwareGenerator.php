<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

/**
 * A generator that tries to generate routes from object, route names or
 * content objects or names.
 *
 * @author Philippo de Santis
 * @author David Buchmann
 * @author Uwe Jäger
 */
class ContentAwareGenerator extends ProviderBasedGenerator
{
    /**
     * The locale to use when neither the parameters nor the request context
     * indicate the locale to use.
     */
    private ?string $defaultLocale = null;

    /**
     * The content repository used to find content by it's id
     * This can be used to specify a parameter content_id when generating urls.
     *
     * This is optional and might not be initialized.
     */
    private ?ContentRepositoryInterface $contentRepository = null;

    /**
     * Set an optional content repository to find content by ids.
     */
    public function setContentRepository(ContentRepositoryInterface $contentRepository): void
    {
        $this->contentRepository = $contentRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name       ignored
     * @param array  $parameters must either contain the field 'route' with a
     *                           RouteObjectInterface or the field 'content_id'
     *                           with the id of a document implementing RouteReferrersReadInterface
     *
     * @throws RouteNotFoundException If there is no such route in the database
     */
    public function generate(string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        if (RouteObjectInterface::OBJECT_BASED_ROUTE_NAME === $name) {
            if (array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters)
                && $parameters[RouteObjectInterface::ROUTE_OBJECT] instanceof SymfonyRoute
            ) {
                $route = $this->getBestLocaleRoute($parameters[RouteObjectInterface::ROUTE_OBJECT], $parameters);
            } else {
                $route = $this->getRouteByContent($name, $parameters);
            }
        } elseif (!empty($name)) {
            $route = $this->getRouteByName($name, $parameters);
        } else {
            $route = $this->getRouteByContent($name, $parameters);
        }

        $this->unsetLocaleIfNotNeeded($route, $parameters);
        $parameters[RouteObjectInterface::ROUTE_OBJECT] = $route;

        return parent::generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, $parameters, $referenceType);
    }

    private function getRouteByName(string $name, array $parameters): SymfonyRoute
    {
        return $this->getBestLocaleRoute($this->provider->getRouteByName($name), $parameters);
    }

    /**
     * Determine if there is a route with matching locale associated with the
     * given route via associated content.
     *
     * @return SymfonyRoute either the passed route or an alternative with better locale
     */
    private function getBestLocaleRoute(SymfonyRoute $route, array $parameters): SymfonyRoute
    {
        if (!$route instanceof RouteObjectInterface) {
            // this route has no content, we can't get the alternatives
            return $route;
        }
        $locale = $this->getLocale($parameters);

        if (!$this->checkLocaleRequirement($route, $locale)) {
            $content = $route->getContent();
            if ($content instanceof RouteReferrersReadInterface) {
                $routes = $content->getRoutes();
                $contentRoute = $this->getRouteByLocale($routes, $locale);
                if ($contentRoute) {
                    return $contentRoute;
                }
            }
        }

        return $route;
    }

    /**
     * Get the route based on the $name that is an object implementing
     * RouteReferrersReadInterface or a content found in the content repository
     * with the content_id specified in parameters that is an instance of
     * RouteReferrersReadInterface.
     *
     * Called in generate when there is no route given in the parameters.
     *
     * If there is more than one route for the content, tries to find the
     * first one that matches the _locale (provided in $parameters or otherwise
     * defaulting to the request locale).
     *
     * If no route with matching locale is found, falls back to just return the
     * first route.
     *
     * @param array $parameters which should contain a content field containing
     *                          a RouteReferrersReadInterface object
     *
     * @return SymfonyRoute the route instance
     *
     * @throws RouteNotFoundException if no route can be determined
     */
    private function getRouteByContent(string $name, array &$parameters): SymfonyRoute
    {
        if (RouteObjectInterface::OBJECT_BASED_ROUTE_NAME === $name
            && array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters)
            && $parameters[RouteObjectInterface::ROUTE_OBJECT] instanceof RouteReferrersReadInterface
        ) {
            $content = $parameters[RouteObjectInterface::ROUTE_OBJECT];
        } elseif (array_key_exists('content_id', $parameters)
            && null !== $this->contentRepository
        ) {
            $content = $this->contentRepository->findById($parameters['content_id']);
            if (null === $content) {
                throw new RouteNotFoundException('The content repository found nothing at id '.$parameters['content_id']);
            }
            if (!$content instanceof RouteReferrersReadInterface) {
                throw new RouteNotFoundException('Content repository did not return a RouteReferrersReadInterface instance for id '.$parameters['content_id']);
            }
        } else {
            throw new RouteNotFoundException(sprintf("The route name argument '%s' is not a RouteReferrersReadInterface instance and there is no 'content_id' parameter", gettype($name)));
        }

        $routes = $content->getRoutes();
        if (0 === count($routes)) {
            $hint = ($this->contentRepository && $this->contentRepository->getContentId($content))
                ? $this->contentRepository->getContentId($content)
                : get_class($content);

            throw new RouteNotFoundException('Content document has no route: '.$hint);
        }

        unset($parameters['content_id']);

        $route = $this->getRouteByLocale($routes, $this->getLocale($parameters));
        if ($route) {
            return $route;
        }

        // if none matched, randomly return the first one
        if ($routes instanceof Collection) {
            return $routes->first();
        }

        return reset($routes);
    }

    /**
     * @param RouteCollection|SymfonyRoute[] $routes
     *
     * @return bool|SymfonyRoute false if no route requirement matches the provided locale
     */
    private function getRouteByLocale(RouteCollection|array $routes, ?string $locale): bool|SymfonyRoute
    {
        foreach ($routes as $route) {
            if (!$route instanceof SymfonyRoute) {
                continue;
            }

            if ($this->checkLocaleRequirement($route, $locale)) {
                return $route;
            }
        }

        return false;
    }

    /**
     * @return bool true if there is either no $locale, no _locale requirement
     *              on the route or if the requirement and the passed $locale
     *              match
     */
    private function checkLocaleRequirement(SymfonyRoute $route, ?string $locale): bool
    {
        return !$locale
            || !$route->getRequirement('_locale')
            || preg_match('/'.$route->getRequirement('_locale').'/', $locale)
        ;
    }

    /**
     * Determine the locale to be used with this request.
     *
     * Look at the parameters and context, fall back to the default locale.
     */
    protected function getLocale(array $parameters): ?string
    {
        if (array_key_exists('_locale', $parameters)) {
            return $parameters['_locale'];
        }

        if ($this->getContext()->hasParameter('_locale')) {
            return $this->getContext()->getParameter('_locale');
        }

        return $this->defaultLocale;
    }

    /**
     * Overwrite the locale to be used by default if there is neither one in
     * the parameters when building the route nor a request available (i.e. CLI).
     */
    public function setDefaultLocale(string $locale): void
    {
        $this->defaultLocale = $locale;
    }

    public function getRouteDebugMessage(string $name, array $parameters = []): string
    {
        if ((!$name || RouteObjectInterface::OBJECT_BASED_ROUTE_NAME === $name)
            && array_key_exists('content_id', $parameters)
        ) {
            return 'Content id '.$parameters['content_id'];
        }

        if (RouteObjectInterface::OBJECT_BASED_ROUTE_NAME === $name
            && array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters)
            && $parameters[RouteObjectInterface::ROUTE_OBJECT] instanceof RouteReferrersReadInterface
        ) {
            return 'Route aware content '.parent::getRouteDebugMessage($name, $parameters);
        }

        return parent::getRouteDebugMessage($name, $parameters);
    }

    /**
     * If the _locale parameter is allowed by the requirements of the route
     * and it is the default locale, remove it from the parameters so that we
     * do not get an unneeded ?_locale= query string.
     *
     * @param SymfonyRoute          $route      The route being generated
     * @param array<string, string> $parameters The parameters used, will be modified to
     *                                          remove the _locale field if needed
     */
    private function unsetLocaleIfNotNeeded(SymfonyRoute $route, array &$parameters): void
    {
        $locale = $this->getLocale($parameters);
        if (null !== $locale
            && preg_match('/'.$route->getRequirement('_locale').'/', $locale)
            && $locale === $route->getDefault('_locale')
        ) {
            $compiledRoute = $route->compile();
            if (!in_array('_locale', $compiledRoute->getVariables(), true)) {
                unset($parameters['_locale']);
            }
        }
    }
}
