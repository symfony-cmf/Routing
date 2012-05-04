<?php

namespace Symfony\Cmf\Component\Routing\Tests\Controller;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;
use Symfony\Cmf\Component\Routing\Resolver\ExplicitTemplateResolver;

class ExplicitTemplateResolverTest extends CmfUnitTestCase
{
    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\RouteObjectInterface',
                                            array('getRouteContent', 'getRouteDefaults', 'getUrl'));

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
