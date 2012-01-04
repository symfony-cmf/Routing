<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Controller;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Default router that handles redirection route objects
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
     * @param RedirectRouteInterface $routeDocument
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse the response
     */
    public function redirectAction($routeDocument)
    {
        if (!$routeDocument) {
            throw new NotFoundHttpException('No route given');
        }

        $url = $this->router->generate($routeDocument->getRouteName(), $routeDocument->getParameters(), true);

        return new RedirectResponse($url);
    }
}
