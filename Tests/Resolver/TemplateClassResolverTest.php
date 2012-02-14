<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Controller;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver\TemplateClassResolver;

class TemplateClassResolverTest extends CmfUnitTestCase
{
    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Routing\\RouteObjectInterface',
                                            array('getRouteContent', 'getRouteDefaults', 'getUrl'));

        $mapping = array('Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Tests\\Controller\\TemplateTargetDocument'
        => 'SomeBundle:Topic:template.html.twig');

        $this->resolver = new TemplateClassResolver('symfony_cmf_content.controller:indexAction', $mapping);
    }

    public function testTemplateFoundInMapping()
    {
        $this->document->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue(new TemplateTargetDocument));

        $defaults = array();
        $this->assertEquals('symfony_cmf_content.controller:indexAction', $this->resolver->getController($this->document, $defaults));
        $this->assertEquals(array('template' => 'SomeBundle:Topic:template.html.twig'), $defaults);
    }

    public function testTemplateNotFoundInMapping()
    {
        $this->document->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue(new TemplateUnknownDocument()));

        $defaults = array();
        $this->assertEquals(null, $this->resolver->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }

    public function testNoContent()
    {
        $this->document->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue(null));

        $defaults = array();
        $this->assertEquals(null, $this->resolver->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }
}

class TemplateTargetDocument
{
}

class TemplateUnknownDocument
{
}