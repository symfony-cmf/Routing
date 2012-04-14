<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Controller;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RedirectRouteInterface;

/**
 * Default router that handles redirection route objects.
 *
 * This is partially a duplication of Symfony\Bundle\FrameworkBundle\Controller\RedirectController
 * but we do not want a dependency on SymfonyFrameworkBundle just for this.
 *
 * The plus side is that with the route interface we do not need to pass the
 * parameters through magic request attributes.
 */
class RedirectController
{
    /**
     * @var RouterInterface
     */
    protected $router;
    /**
     * @param RouterInterface $router the router to use to build urls
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Action to redirect based on a RedirectRouteInterface route
     *
     * @param RedirectRouteInterface $contentDocument
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse the response
     */
    public function redirectAction(RedirectRouteInterface $contentDocument)
    {
        $url = $contentDocument->getUri();

        if (empty($url)) {
            $url = $this->router->generate($contentDocument->getRouteName(), $contentDocument->getParameters(), true);
        }

        return new RedirectResponse($url, $contentDocument->isPermanent() ? 301 : 302);
    }
}
