<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Routing;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\ContentRouter;

class ContentRouterTest extends CmfUnitTestCase
{
    public function setUp()
    {
        $this->node = $this->buildMock('Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface');
        $this->document = $this->buildMock('Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface');
        $this->loader = $this->buildMock("Symfony\Component\Config\Loader\LoaderInterface");
        $this->om = $this->buildMock("Doctrine\Common\Persistence\ObjectManager");
        $this->resolver = $this->buildMock('Symfony\Cmf\Bundle\ChainRoutingBundle\Controller\ControllerResolver', array('getController'));

        $this->router = new ContentRouter($this->om, $this->resolver);
        $this->router->setObjectManager($this->om);
        $this->router->setControllerResolver($this->resolver);
    }

    public function testMatch()
    {
        $url_alias = "/company/more";

        $this->node->expects($this->once())
                ->method('getReference')
                ->will($this->returnValue($this->document));

        $this->om->expects($this->once())
                ->method('find')
                ->with(null, $url_alias)
                ->will($this->returnValue($this->node));

        $expected = array('_controller' => 'NameSpace\Controller::action',
                                                'type' => 'found',
                                                'reference' => $this->document);

        $this->resolver->expects($this->once())
                ->method('getController')
                ->with($this->node)
                ->will($this->returnValue($expected));

        $results = $this->router->match($url_alias);

        $this->assertEquals($expected, $results);
    }

    public function testNoReferenceMatch()
    {
        $url_alias = "/company/more_no_reference";

        $this->node->expects($this->once())
                ->method('getReference')
                ->will($this->returnValue(null));

        $this->resolver->expects($this->once())
                ->method('getController')
                ->with($this->document)
                ->will($this->returnValue(array('_controller' => 'NameSpace\Controller::action', 'type' => 'found')));

        $this->om->expects($this->once())
                ->method('find')
                ->with(null, $url_alias)
                ->will($this->returnValue($this->node));

        $expected = array('_controller' => 'NameSpace\Controller::action',
                          'reference' => null,
                          'type' => 'found');

        $this->assertEquals($expected, $this->router->match($url_alias));
    }

    public function testNoNodeMatch()
    {
        $url_alias = "/company/more_no_match";

        $this->om->expects($this->once())
                ->method('find')
                ->with(null, $url_alias)
                ->will($this->returnValue(null));

        $this->assertFalse($this->router->match($url_alias));
    }
}
