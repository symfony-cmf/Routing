<?php

namespace Symfony\Cmf\Component\Routing\Tests\Enhancer;

use Symfony\Cmf\Component\Routing\Enhancer\ConditionalEnhancer;
use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class ConditionalEnhancerTest extends CmfUnitTestCase
{
    private $request;

    /**
     * @var ConditionalEnhancer
     */
    private $mapper;
    private $enhancerWithoutParameters;
    private $enhancerOne;
    private $lonelyEnhancer;
    private $methodAwareMapper;

    public function setUp()
    {
        $mapping = array(
            'enhancer_one' => array(
                'foo',
                array('methods' => array('post', 'put'), 'value' => 'ba'),
            ),
        );

        $this->mapper = new ConditionalEnhancer($mapping);

        $this->enhancerOne = $this->buildMock(
            'Symfony\Cmf\Component\Routing\Tests\Enhancer\EnhancerWithMapping',
            array('enhance', 'isName', 'setMapping')
        );
        $this->methodAwareMapper = $this->buildMock(
            'Symfony\Cmf\Component\Routing\Tests\Enhancer\EnhancerWithMapping',
            array('enhance', 'isName', 'setMapping')
        );
        $this->lonelyEnhancer = $this->buildMock(
            'Symfony\Cmf\Component\Routing\Tests\Enhancer\EnhancerWithMapping',
            array('enhance', 'isName', 'setMapping')
        );
        $this->enhancerWithoutParameters = $this->buildMock(
            'Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface',
            array('enhance')
        );
        $this->mapper->addRouteEnhancer($this->enhancerOne);
        $this->mapper->addRouteEnhancer($this->lonelyEnhancer);
        $this->mapper->addRouteEnhancer($this->enhancerWithoutParameters);

        $this->enhancerOne->expects($this->any())
            ->method('isName')
            ->will($this->returnCallback(function ($name) {
                return 'enhancer_one' === $name;
            }));

        $this->lonelyEnhancer->expects($this->any())
            ->method('isName')
            ->will($this->returnCallback(function ($name) {
                return 'lonely' === $name;
            }));

        $this->request = Request::create('/test', 'POST');
    }

    public function testCalledEnhancers()
    {
        $this->enhancerOne->expects($this->once())
            ->method('enhance')
            ->will($this->returnValue(array()))
        ;
        $this->enhancerWithoutParameters->expects($this->once())
            ->method('enhance')
            ->will($this->returnValue(array()))
        ;
        $this->lonelyEnhancer->expects($this->never())
            ->method('enhance');

        $this->mapper->enhance(array(), $this->request);
    }

    public function testParametersSetting()
    {
        $this->enhancerOne->expects($this->once())
            ->method('setMapping')
            ->with($this->equalTo(array('foo', 'ba')));

        $this->enhancerOne->expects($this->once())
            ->method('enhance')
            ->will($this->returnValue(array()))
        ;
        $this->enhancerWithoutParameters->expects($this->once())
            ->method('enhance')
            ->will($this->returnValue(array()))
        ;

        $this->mapper->enhance(array(), $this->request);
    }
}
