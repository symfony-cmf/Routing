<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Routing;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\DoctrineRouter;

class DoctrineRouterTest extends CmfUnitTestCase
{
    public function setUp()
    {
        $this->node = $this->buildMock('Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface');
        $this->document = $this->buildMock('Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface');
        $this->loader = $this->buildMock("Symfony\Component\Config\Loader\LoaderInterface");
        $this->om = $this->buildMock("Doctrine\Common\Persistence\ObjectManager");
        $this->resolver = $this->buildMock('Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver\ControllerResolverInterface', array('getController'));

        $this->router = new DoctrineRouter($this->om);
        $this->router->setObjectManager($this->om);
        $this->router->addControllerResolver($this->resolver);
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
                          'reference' => $this->document);

        $this->resolver->expects($this->once())
                ->method('getController')
                ->with($this->node)
                ->will($this->returnValue('NameSpace\Controller::action'));

        $results = $this->router->match($url_alias);

        $this->assertEquals($expected, $results);
    }

    public function testNoReferenceMatch()
    {
        $url_alias = "/company/more_no_reference";

        $this->node->expects($this->once())
                ->method('getReference')
                ->will($this->returnValue(null));
        $this->node->expects($this->once())
                ->method('getRouteDefaults')
                ->will($this->returnValue(array('type' => 'found')));

        $this->resolver->expects($this->once())
                ->method('getController')
                ->with($this->document)
                ->will($this->returnValue('NameSpace\Controller::action'));

        $this->om->expects($this->once())
                ->method('find')
                ->with(null, $url_alias)
                ->will($this->returnValue($this->node));

        $expected = array('_controller' => 'NameSpace\Controller::action',
                          'reference' => null,
                          'type' => 'found');

        $this->assertEquals($expected, $this->router->match($url_alias));
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
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
