<?php

namespace Symfony\Cmf\Component\Routing\Tests\Mapper;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;
use Symfony\Cmf\Component\Routing\Mapper\ControllerAliasMapper;

class ControllerAliasMapperTest extends CmfUnitTestCase
{
    /**
     * @var ControllerAliasMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Tests\\Mapper\\RouteObject',
                                            array('getRouteContent', 'getRouteDefaults', 'getUrl'));

        $mapping = array('static_pages' => 'symfony_cmf_content.controller:indexAction');

        $this->mapper = new ControllerAliasMapper($mapping);
    }

    public function testControllerFoundInMapping()
    {
        $defaults = array('type' => 'static_pages');
        $this->assertEquals('symfony_cmf_content.controller:indexAction', $this->mapper->getController($this->document, $defaults));
    }

    public function testControllerNoType()
    {
        $defaults = array();
        $this->assertEquals(null, $this->mapper->getController($this->document, $defaults));
    }

    public function testControllerNotFoundInMapping()
    {
        $defaults = array('type' => 'unknown_route');
        $this->assertEquals(null, $this->mapper->getController($this->document, $defaults));
    }
}
