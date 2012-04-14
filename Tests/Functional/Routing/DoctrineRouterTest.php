<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\Routing;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Document\Route;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\BaseTestCase;

/**
 * The goal of these tests is to test the interoperation with DI and everything.
 * We do not aim to cover all edge cases and exceptions - that is was the unit
 * test is here for.
 */
class DoctrineRouterTest extends BaseTestCase
{
    /**
     * @var \Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\ChainRouter
     */
    protected static $router;

    const ROUTE_ROOT = '/test/routing';

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass(array(), basename(self::ROUTE_ROOT));
        self::$router = self::$kernel->getContainer()->get('router');

        $route = new Route;
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        // do not set a content here, or we need a valid request and so on...
        $route->setPosition($root, 'testroute');
        $route->setDefault('_controller', 'testController');
        self::$dm->persist($route);
        self::$dm->flush();
    }

    public function testMatch()
    {
        $expected = array(
            '_controller'   => 'testController',
            '_route'        => 'chain_router_doctrine_route_testroute',
            'path'          => '/testroute',
        );

        $matches = self::$router->match('/testroute');
        ksort($matches);
        $this->assertEquals($expected, $matches);
    }

    public function testGenerate()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/testroute');
        $url = self::$router->generate('', array('route' => $route));
        $this->assertEquals('/testroute', $url);
    }
}
