<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\Document;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Document\Route;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\BaseTestCase;

class RouteTest extends BaseTestCase
{
    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager
     */
    protected static $dm;

    const ROUTE_ROOT = '/test/route';

    public static function setupBeforeClass()
    {
        self::$kernel = self::createKernel();
        self::$kernel->boot();

        $session = self::$kernel->getContainer()->get('doctrine_phpcr.session');
        if ($session->nodeExists(self::ROUTE_ROOT)) {
            $session->getNode(self::ROUTE_ROOT)->remove();
        }
        if (! $session->nodeExists('/test')) {
            $session->getRootNode()->addNode('test', 'nt:unstructured');
        }
        $baseroute = $session->getNode('/test')->addNode('route', 'nt:unstructured');
        $baseroute->addMixin('mix:referenceable');
        $session->save();

        self::$dm = self::$kernel->getContainer()->get('doctrine_phpcr.odm.document_manager');
    }

    /**
     *
     */
    public function testPersist()
    {
        $route = new Route;
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $route->setRouteContent($root); // this happens to be a referenceable node
        $route->setPosition($root, 'testroute');
        $route->setPattern('/testroute');
        $route->setDefault('x', 'y');
        $route->setRequirement('testreq', 'testregex');
        $route->setOptions(array('test' => 'value'));
        $route->setOption('another', 'value2');
        self::$dm->persist($route);
        self::$dm->flush();

        self::$dm->clear();

        $route = self::$dm->find(null, self::ROUTE_ROOT.'/testroute');

        $this->assertNotNull($route->getRouteContent());
        $this->assertEquals('/testroute', $route->getPattern());

        $this->assertEquals('y', $route->getDefault('x'));
        $defaults = $route->getDefaults();
        $this->assertArrayHasKey('x', $defaults);
        $this->assertEquals('y', $defaults['x']);

        $requirements = $route->getRequirements();
        $this->assertArrayHasKey('testreq', $requirements);
        $this->assertEquals('testregex', $requirements['testreq']);

        $options = $route->getOptions();
        $this->assertArrayHasKey('test', $options);
        $this->assertEquals('value', $options['test']);
        $this->assertArrayHasKey('another', $options);
        $this->assertEquals('value2', $options['another']);
    }

    public function testPersistEmpty()
    {
        $route = new Route;
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $route->setPosition($root, 'empty');
        self::$dm->persist($route);
        self::$dm->flush();

        self::$dm->clear();

        $route = self::$dm->find(null, self::ROUTE_ROOT.'/empty');

        $defaults = $route->getDefaults();
        $this->assertCount(0, $defaults);

        $requirements = $route->getRequirements();
        $this->assertCount(0, $requirements);

        $options = $route->getOptions();
        $this->assertTrue(1 >= count($options)); // there is a default option for the compiler
    }
}
