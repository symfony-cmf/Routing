<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Controller;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RedirectRouteInterface;

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
     * @param RedirectRouteInterface $contentDocument
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse the response
     */
    public function redirectAction(RedirectRouteInterface $contentDocument)
    {
        if (! $contentDocument) {
            throw new NotFoundHttpException('No route given');
        }

        $url = $contentDocument->getUri();

        if (empty($url)) {
            $url = $this->router->generate($contentDocument->getRouteName(), $contentDocument->getParameters(), true);
        }

        return new RedirectResponse($url);
    }
}
