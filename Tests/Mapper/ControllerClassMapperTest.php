<?php

namespace Symfony\Cmf\Component\Routing\Tests\Mapper;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;
use Symfony\Cmf\Component\Routing\Mapper\ControllerClassMapper;

class ControllerClassMapperTest extends CmfUnitTestCase
{
    /**
     * @var ControllerClassMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Tests\\Mapper\\RouteObject',
                                            array('getRouteContent', 'getRouteDefaults', 'getUrl'));

        $mapping = array('Symfony\\Cmf\\Component\\Routing\\Tests\\Mapper\\TargetDocument'
                            => 'symfony_cmf_content.controller:indexAction');

        $this->mapper = new ControllerClassMapper($mapping);
    }

    public function testControllerFoundInMapping()
    {
        $this->document->expects($this->once())
                ->method('getRouteContent')
                ->will($this->returnValue(new TargetDocument));

        $defaults = array();
        $this->assertEquals('symfony_cmf_content.controller:indexAction', $this->mapper->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }

    public function testControllerNotFoundInMapping()
    {
        $this->document->expects($this->once())
                ->method('getRouteContent')
                ->will($this->returnValue(new UnknownDocument));

        $defaults = array();
        $this->assertEquals(null, $this->mapper->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }

    public function testControllerNoContent()
    {
        $this->document->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue(null));

        $defaults = array();
        $this->assertEquals(null, $this->mapper->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }
}

class TargetDocument
{
}

class UnknownDocument
{
}