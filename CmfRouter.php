<?php

namespace Symfony\Cmf\Component\Routing;

use Symfony\Component\Routing\Route as SymfonyRoute;

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
class CmfRouter extends DynamicRouter
{
    /**
     * Support empty name, any strings, route aware content and route objects
     *
     * {@inheritDoc}
     */
    public function supports($name)
    {
        return !$name || is_string($name) || $name instanceof RouteAwareInterface || $name instanceof SymfonyRoute;
    }
}
