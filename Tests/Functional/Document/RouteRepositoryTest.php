<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\Document;

use Doctrine\ODM\PHPCR\Document\Generic;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Document\Route;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\BaseTestCase;

class RouteRepositoryTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/routing';

    private static $repository;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass(array(), basename(self::ROUTE_ROOT));
        self::$repository = self::$kernel->getContainer()->get('symfony_cmf_chain_routing.phpcrodm_route_repository');
    }

    public function testFindManyByUrl()
    {
        $route = new Route;
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $route->setPosition($root, 'testroute');
        self::$dm->persist($route);

        // smuggle a non-route thing into the repository
        $noroute = new Generic;
        $noroute->setParent($route);
        $noroute->setNodename('noroute');
        self::$dm->persist($noroute);

        $childroute = new Route;
        $childroute->setPosition($noroute, 'child');
        self::$dm->persist($childroute);

        self::$dm->flush();

        self::$dm->clear();

        $routes = self::$repository->findManyByUrl('/testroute/noroute/child');
        $this->assertCount(3, $routes);

        foreach ($routes as $route) {
            $this->assertInstanceOf('Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface', $route);
        }
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testFindInvalidUrl()
    {
        self::$repository->findManyByUrl('x');
    }

    public function testFindNophpcrUrl()
    {
        $this->assertNull(self::$repository->findManyByUrl('///'));
    }

    public function testSetPrefix()
    {
        self::$repository->setPrefix(self::ROUTE_ROOT);
    }
}
