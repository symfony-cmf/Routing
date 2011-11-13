<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Controller;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver\ControllerAliasResolver;

class ControllerAliasResolverTest extends CmfUnitTestCase
{
    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface',
                                            array('getReference', 'getRouteDefaults'));

        $mapping = array('static_pages' => 'symfony_cmf_content.controller:indexAction');

        $this->resolver = new ControllerAliasResolver($mapping);
    }

    public function testControllerFoundInMapping()
    {
        $this->document->expects($this->once())
                ->method('getRouteDefaults')
                ->will($this->returnValue(array('type' => 'static_pages', '_controller' => '::default.html.twig')));

        $this->assertEquals('symfony_cmf_content.controller:indexAction', $this->resolver->getController($this->document));
    }

    public function testControllerNotFoundInMapping()
    {
        $this->document->expects($this->once())
                ->method('getRouteDefaults')
                ->will($this->returnValue(array('type' => 'unknown_route', '_controller' => '::default.html.twig')));


        $this->assertEquals(null, $this->resolver->getController($this->document));
    }
}
