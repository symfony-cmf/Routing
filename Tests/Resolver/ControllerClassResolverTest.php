<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Controller;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver\ControllerClassResolver;

class ControllerClassResolverTest extends CmfUnitTestCase
{
    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface',
                                            array('getReference', 'getRouteDefaults'));

        $mapping = array('Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Controller\TargetDocument'
                            => 'symfony_cmf_content.controller:indexAction');

        $this->resolver = new ControllerClassResolver($mapping);
    }

    public function testControllerFoundInMapping()
    {
        $this->document->expects($this->once())
                ->method('getReference')
                ->will($this->returnValue(new TargetDocument));

        $this->assertEquals('symfony_cmf_content.controller:indexAction', $this->resolver->getController($this->document));
    }

    public function testControllerNotFoundInMapping()
    {
        $this->document->expects($this->once())
                ->method('getReference')
                ->will($this->returnValue(new UnknownDocument));

        $this->assertEquals(null, $this->resolver->getController($this->document));
    }
}

class TargetDocument
{
}

class UnknownDocument
{
}