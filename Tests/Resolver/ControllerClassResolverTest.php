<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Controller;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver\ControllerClassResolver;

class ControllerClassResolverTest extends CmfUnitTestCase
{
    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface',
                                            array('getRouteContent', 'getRouteDefaults', 'getPath'));

        $mapping = array('Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Controller\TargetDocument'
                            => 'symfony_cmf_content.controller:indexAction');

        $this->resolver = new ControllerClassResolver($mapping);
    }

    public function testControllerFoundInMapping()
    {
        $this->document->expects($this->once())
                ->method('getRouteContent')
                ->will($this->returnValue(new TargetDocument));

        $defaults = array();
        $this->assertEquals('symfony_cmf_content.controller:indexAction', $this->resolver->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }

    public function testControllerNotFoundInMapping()
    {
        $this->document->expects($this->once())
                ->method('getRouteContent')
                ->will($this->returnValue(new UnknownDocument));

        $defaults = array();
        $this->assertEquals(null, $this->resolver->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }

    public function testControllerNoContent()
    {
        $this->document->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue(null));

        $defaults = array();
        $this->assertEquals(null, $this->resolver->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }
}

class TargetDocument
{
}

class UnknownDocument
{
}