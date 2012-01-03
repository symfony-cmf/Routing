<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Controller;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver\ExplicitTemplateResolver;

class ExplicitTemplateResolverTest extends CmfUnitTestCase
{
    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Routing\\RouteObjectInterface',
                                            array('getRouteContent', 'getRouteDefaults', 'getPath'));

        $this->resolver = new ExplicitTemplateResolver('symfony_cmf_content.controller:indexAction');
    }

    public function testHasTemplate()
    {
        $defaults = array('template' => 'Bundle:Topic:template.html.twig');
        $this->assertEquals('symfony_cmf_content.controller:indexAction', $this->resolver->getController($this->document, $defaults));
        $this->assertEquals(array('template' => 'Bundle:Topic:template.html.twig'), $defaults);
    }

    public function testHasNoTemplate()
    {
        $defaults = array();
        $this->assertEquals(null, $this->resolver->getController($this->document, $defaults));
        $this->assertEquals(array(), $defaults);
    }
}
