<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\Document;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Document\Route;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\BaseTestCase;

class RouteRepositoryTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/routing';

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass(array(), basename(self::ROUTE_ROOT));
    }

    /**
     *
     */
    public function testFindManyByUrl()
    {
        $route = new Route;
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $route->setPosition($root, 'testroute');
        $route->setPattern('/testroute');
        self::$dm->persist($route);

        $childroute = new Route;
        $childroute->setPosition($route, 'child');
        $route->setPattern('/testroute/child');
        self::$dm->persist($childroute);
        self::$dm->flush();

        self::$dm->clear();

        $routes = self::$kernel->getContainer()->get('symfony_cmf_chain_routing.phpcrodm_route_repository')->findManyByUrl('/testroute/child');
        $this->assertCount(3, $routes);

        foreach ($routes as $route) {
            $this->assertInstanceOf('Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface', $route);
        }
    }
}
