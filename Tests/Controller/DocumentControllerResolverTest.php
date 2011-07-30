<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Controller;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Controller\DocumentControllerResolver;

class DocumentControllerResolverTest extends CmfUnitTestCase
{
    public function setUp()
    {
        $this->document = $this->buildMock('Document', array('getController'));
        $mapping = array('static_pages' => 'symfony_cmf_content.controller:indexAction');

        $this->resolver = new DocumentControllerResolver($mapping);
    }

    public function testControllerFoundInMapping()
    {
        $this->document->expects($this->once())
               ->method('getController')
               ->will($this->returnValue('static_pages'));
        
        $this->assertEquals('symfony_cmf_content.controller:indexAction', $this->resolver->getController($this->document));
    }
    
    public function testControllerNotFoundInMapping()
    {
        $this->document->expects($this->once())
               ->method('getController')
               ->will($this->returnValue('unknown_alias'));

        $this->assertEquals(null, $this->resolver->getController($this->document));
    }

}
