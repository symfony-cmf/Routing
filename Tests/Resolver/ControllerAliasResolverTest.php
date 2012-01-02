<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Controller;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver\ControllerAliasResolver;

class ControllerAliasResolverTest extends CmfUnitTestCase
{
    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface',
                                            array('getRouteContent', 'getRouteDefaults', 'getPath'));

        $mapping = array('static_pages' => 'symfony_cmf_content.controller:indexAction');

        $this->resolver = new ControllerAliasResolver($mapping);
    }

    public function testControllerFoundInMapping()
    {
        $defaults = array('type' => 'static_pages');
        $this->assertEquals('symfony_cmf_content.controller:indexAction', $this->resolver->getController($this->document, $defaults));
    }

    public function testControllerNoType()
    {
        $defaults = array();
        $this->assertEquals(null, $this->resolver->getController($this->document, $defaults));
    }

    public function testControllerNotFoundInMapping()
    {
        $defaults = array('type' => 'unknown_route');
        $this->assertEquals(null, $this->resolver->getController($this->document, $defaults));
    }
}
