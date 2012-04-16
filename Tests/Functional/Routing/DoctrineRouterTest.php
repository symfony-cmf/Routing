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
        $route->setVariablePattern('/{slug}/{id}');
        $route->setDefault('id', '0');
        $route->setRequirement('id', '[0-9]+');
        $route->setDefault('_controller', 'testController');
        // TODO: what are the options used for? we should test them too if it makes sense
        self::$dm->persist($route);

        $childroute = new Route;
        $childroute->setPosition($route, 'child');
        $childroute->setDefault('_controller', 'testController');
        self::$dm->persist($childroute);

        self::$dm->flush();
    }

    public function testMatch()
    {
        $expected = array(
            '_controller'   => 'testController',
            '_route'        => 'chain_router_doctrine_route_test_routing_testroute_child',
            'path'          => '/testroute/child',
        );

        $matches = self::$router->match('/testroute/child');
        ksort($matches);
        $this->assertEquals($expected, $matches);
    }

    public function testMatchParameters()
    {
        $expected = array(
            '_controller'   => 'testController',
            '_route'        => 'chain_router_doctrine_route_test_routing_testroute',
            'id'            => '123',
            'path'          => '/testroute/child/123',
            'slug'          => 'child',
        );

        $matches = self::$router->match('/testroute/child/123');
        ksort($matches);
        $this->assertEquals($expected, $matches);
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testNoMatch()
    {
        self::$router->match('/testroute/child/123a');
    }

    public function testGenerate()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/testroute/child');

        $url = self::$router->generate('', array('route' => $route, 'test' => 'value'));
        $this->assertEquals('/testroute/child?test=value', $url);
    }

    public function testGenerateAbsolute()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/testroute/child');
        $url = self::$router->generate('', array('route' => $route, 'test' => 'value'), true);
        $this->assertEquals('http://localhost/testroute/child?test=value', $url);
    }

    public function testGenerateParameters()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/testroute');

        $url = self::$router->generate('', array('route' => $route, 'slug' => 'gen-slug', 'test' => 'value'));
        $this->assertEquals('/testroute/gen-slug?test=value', $url);
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function testGenerateParametersInvalid()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/testroute');

        self::$router->generate('', array('route' => $route, 'slug' => 'gen-slug', 'id' => 'nonumber'));
    }
}
