<?php

namespace Symfony\Cmf\Component\Routing\Tests\Mapper;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;
use Symfony\Cmf\Component\Routing\Mapper\TemplateClassMapper;

class TemplateClassMapperTest extends CmfUnitTestCase
{
    /**
     * @var TemplateClassMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Tests\\Mapper\\RouteObject',
                                            array('getRouteContent', 'getRouteDefaults', 'getUrl'));

        $mapping = array('Symfony\\Cmf\\Component\\Routing\\Tests\\Mapper\\TemplateTargetDocument'
        => 'SomeBundle:Topic:template.html.twig');

        $this->mapper = new TemplateClassMapper('symfony_cmf_content.controller:indexAction', $mapping);
    }

    public function testTemplateFoundInMapping()
    {
        $this->document->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue(new TemplateTargetDocument));

        $defaults = array();
        $this->assertEquals('symfony_cmf_content.controller:indexAction', $this->mapper->getController($this->document, $defaults));
        $this->assertEquals(array('_template' => 'SomeBundle:Topic:template.html.twig'), $defaults);
    }

    public function testTemplateNotFoundInMapping()
    {
        $this->document->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue(new TemplateUnknownDocument()));

        $defaults = array();
        $this->assertEquals(null, $this->mapper->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }

    public function testNoContent()
    {
        $this->document->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue(null));

        $defaults = array();
        $this->assertEquals(null, $this->mapper->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }
}

class TemplateTargetDocument
{
}

class TemplateUnknownDocument
{
}
