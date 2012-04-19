<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\Controller;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\BaseTestCase;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Document\Route;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Document\RedirectRoute;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Controller\RedirectController;

class RedirectControllerTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/routing';

    /**
     * @var \Symfony\Cmf\Bundle\ChainRoutingBundle\Controller\RedirectController
     */
    protected static $controller;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass(array(), basename(self::ROUTE_ROOT));
        $router = self::$kernel->getContainer()->get('router');
        self::$controller = new RedirectController($router);
    }

    public function testRedirectUri()
    {
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $redirect = new RedirectRoute;
        $redirect->setPosition($root, 'redirectUri');
        $redirect->setUri('http://example.com/test-url');
        $redirect->setParameters(array('test'=>7)); // parameters should be ignored in this case
        $redirect->setPermanent(true);
        self::$dm->persist($redirect);

        self::$dm->flush();

        self::$dm->clear();

        $redirect = self::$dm->find(null, self::ROUTE_ROOT.'/redirectUri');
        $response = self::$controller->redirectAction($redirect);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://example.com/test-url', $response->getTargetUrl());
    }

    public function testRedirectContent()
    {
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $route = new Route;
        $route->setRouteContent($root); // this happens to be a referenceable node
        $route->setPosition($root, 'testroute');
        self::$dm->persist($route);

        $redirect = new RedirectRoute;
        $redirect->setPosition($root, 'redirectContent');
        $redirect->setRouteTarget($route);
        $redirect->setParameters(array('test' => 'content'));
        self::$dm->persist($redirect);

        self::$dm->flush();

        self::$dm->clear();

        $redirect = self::$dm->find(null, self::ROUTE_ROOT.'/redirectContent');
        $response = self::$controller->redirectAction($redirect);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/testroute?test=content', $response->getTargetUrl());
    }

    public function testRedirectName()
    {
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $redirect = new RedirectRoute;
        $redirect->setPosition($root, 'redirectName');
        $redirect->setRouteName('symfony_route');
        $redirect->setParameters(array('param'=>7)); // parameters should be ignored in this case
        self::$dm->persist($redirect);

        self::$dm->flush();

        self::$dm->clear();

        $redirect = self::$dm->find(null, self::ROUTE_ROOT.'/redirectName');
        $response = self::$controller->redirectAction($redirect);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/symfony_route_test?param=7', $response->getTargetUrl());
    }
}