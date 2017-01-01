<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Mapper;

use Symfony\Cmf\Component\Routing\Enhancer\ConditionalEnhancer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class ConditionalEnhancerTest extends CmfUnitTestCase
{
    /**
     * @var Request
     */
    private $request;

    public function setUp()
    {
        $this->request = Request::create('/test');
    }

    public function testSecondMatch()
    {
        $defaults = array('foo' => 'bar');
        $expected = array('matcher' => 'found');

        $enhancer1 = $this->buildMock('\Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface');
        $enhancer1->expects($this->never())
            ->method('enhance');
        $matcher1 = $this->buildMock('\Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $matcher1->expects($this->once())
            ->method('matches')
            ->with($this->request)
            ->will($this->returnValue(false));

        $enhancer2 = $this->buildMock('\Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface');
        $enhancer2->expects($this->once())
            ->method('enhance')
            ->with($defaults, $this->request)
            ->will($this->returnValue($expected));
        $matcher2 = $this->buildMock('\Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $matcher2->expects($this->once())
            ->method('matches')
            ->with($this->request)
            ->will($this->returnValue(true));

        $enhancer = new ConditionalEnhancer(array(
           array(
               'matcher' => $matcher1,
               'enhancer' => $enhancer1,
           ),
            array(
                'matcher' => $matcher2,
                'enhancer' => $enhancer2,
            ),
        ));

        $this->assertEquals($expected, $enhancer->enhance($defaults, $this->request));
    }

    public function testNoMatch()
    {
        $defaults = array('foo' => 'bar');
        $expected = array('matcher' => 'found');

        $enhancer1 = $this->buildMock('\Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface');
        $enhancer1->expects($this->never())
            ->method('enhance');
        $matcher1 = $this->buildMock('\Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $matcher1->expects($this->once())
            ->method('matches')
            ->with($this->request)
            ->will($this->returnValue(false));

        $enhancer = new ConditionalEnhancer(array(
            array(
                'matcher' => $matcher1,
                'enhancer' => $enhancer1,
            ),
        ));

        $this->assertEquals($defaults, $enhancer->enhance($defaults, $this->request));
    }
}
