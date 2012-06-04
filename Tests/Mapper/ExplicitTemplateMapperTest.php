<?php

namespace Symfony\Cmf\Component\Routing\Tests\Mapper;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\Mapper\ExplicitTemplateMapper;

class ExplicitTemplateMapperTest extends CmfUnitTestCase
{
    /**
     * @var ExplicitTemplateMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Tests\\Mapper\\RouteObject',
                                            array('getRouteContent', 'getRouteDefaults', 'getUrl'));

        $this->mapper = new ExplicitTemplateMapper('symfony_cmf_content.controller:indexAction');
    }

    public function testHasTemplate()
    {
        $defaults = array(RouteObjectInterface::TEMPLATE_NAME => 'Bundle:Topic:template.html.twig');
        $this->assertEquals('symfony_cmf_content.controller:indexAction', $this->mapper->getController($this->document, $defaults));
        $this->assertEquals(array(RouteObjectInterface::TEMPLATE_NAME => 'Bundle:Topic:template.html.twig'), $defaults);
    }

    public function testHasNoTemplate()
    {
        $defaults = array();
        $this->assertEquals(null, $this->mapper->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }
}
