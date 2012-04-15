<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\Document;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Document\Route;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional\BaseTestCase;

class RouteTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/routing';

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass(array(), basename(self::ROUTE_ROOT));
    }

    public function testPersist()
    {
        $route = new Route;
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $route->setRouteContent($root); // this happens to be a referenceable node
        $route->setPosition($root, 'testroute');
        $route->setDefault('x', 'y');
        $route->setRequirement('testreq', 'testregex');
        $route->setOptions(array('test' => 'value'));
        $route->setOption('another', 'value2');

        self::$dm->persist($route);
        self::$dm->flush();
        $this->assertEquals('/testroute', $route->getPattern());

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

    public function testPersistEmptyOptions()
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

        return $route;
    }

    public function testRootRoute()
    {
        $root = self::$dm->find(null, self::ROUTE_ROOT);
        $this->assertEquals('/', $root->getPattern());
    }

    public function testSetPattern()
    {
        $root = self::$dm->find(null, self::ROUTE_ROOT);
        $root->setPattern('/{test}');
        $this->assertEquals('{test}', $root->getVariablePattern());
    }

    /**
     * @depends testPersistEmptyOptions
     *
     * @expectedException InvalidArgumentException
     */
    public function testSetPatternInvalid($route)
    {
        $route->setPattern('/impossible');
    }

    /**
     * @expectedException LogicException
     */
    public function testInvalidIdPrefix()
    {
        $root = self::$dm->find(null, self::ROUTE_ROOT);
        $root->setPrefix('/changed'); // simulate a problem with the prefix setter listener
        $this->assertEquals('/', $root->getPattern());
    }

    /**
     * @expectedException LogicException
     */
    public function testPrefixNonpersisted()
    {
        $route = new Route;
        $route->getPattern();
    }
}
