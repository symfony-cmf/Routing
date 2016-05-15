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
use Symfony\Cmf\Component\Routing\Enhancer\FieldByClassEnhancer;
use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;
use Symfony\Cmf\Component\Testing\Document\Content;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;

class ConditionalEnhancerTest extends \PHPUnit_Framework_TestCase
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
        $defaults = ['foo' => 'bar'];
        $expected = ['matcher' => 'found'];

        $enhancer1 = $this->createMock(RouteEnhancerInterface::class);
        $enhancer1->expects($this->never())
            ->method('enhance');
        $matcher1 = $this->createMock(RequestMatcherInterface::class);
        $matcher1->expects($this->once())
            ->method('matches')
            ->with($this->request)
            ->will($this->returnValue(false));

        $enhancer2 = $this->createMock(RouteEnhancerInterface::class);
        $enhancer2->expects($this->once())
            ->method('enhance')
            ->with($defaults, $this->request)
            ->will($this->returnValue($expected));
        $matcher2 = $this->createMock(RequestMatcherInterface::class);
        $matcher2->expects($this->once())
            ->method('matches')
            ->with($this->request)
            ->will($this->returnValue(true));

        $enhancer = new ConditionalEnhancer([
            [
                'matcher' => $matcher1,
                'enhancer' => $enhancer1,
            ],
            [
                'matcher' => $matcher2,
                'enhancer' => $enhancer2,
            ],
        ]);

        $this->assertEquals($expected, $enhancer->enhance($defaults, $this->request));
    }

    public function testNoMatch()
    {
        $defaults = ['foo' => 'bar'];

        $enhancer1 = $this->createMock(RouteEnhancerInterface::class);
        $enhancer1->expects($this->never())
            ->method('enhance');
        $matcher1 = $this->createMock(RequestMatcherInterface::class);
        $matcher1->expects($this->once())
            ->method('matches')
            ->with($this->request)
            ->will($this->returnValue(false));

        $enhancer = new ConditionalEnhancer([
            [
                'matcher' => $matcher1,
                'enhancer' => $enhancer1,
            ],
        ]);

        $this->assertEquals($defaults, $enhancer->enhance($defaults, $this->request));
    }

    public function testCreatorMethod()
    {
        $actual = ConditionalEnhancer::createMapEntry(
            '\Symfony\Cmf\Component\Routing\Enhancer\FieldByClassEnhancer',
            'source',
            'target',
            array('foo' => 'ba'),
            array('put', 'post')
        );
        $expected = array(
            'matcher' => new RequestMatcher(null, null, array('put', 'post')),
            'enhancer' => new FieldByClassEnhancer('source', 'target', array('foo' => 'ba')),
        );

        $this->assertEquals($expected, $actual);
    }

    public function testCreateAndAddWithPosition()
    {
        $conditionalEnhancer = new ConditionalEnhancer();
        $conditionalEnhancer->createAndAddMapEntry(
            '\Symfony\Cmf\Component\Routing\Enhancer\FieldByClassEnhancer',
            '_content',
            '_controller',
            ['\Symfony\Cmf\Component\Testing\Document\Content' => 'service:indexAction'],
            array('put'),
            1
        );
        $conditionalEnhancer->createAndAddMapEntry(
            '\Symfony\Cmf\Component\Routing\Enhancer\FieldByClassEnhancer',
            '_content',
            '_controller',
            ['\Symfony\Cmf\Component\Testing\Document\Content' => 'service:putAction'],
            array('put'),
            2
        );
        $request = Request::create(null, 'PUT');
        $defaults = array('_content' => new Content());
        $actualDefaults = $conditionalEnhancer->enhance($defaults, $request);
        $expectedDefaults = array(
            '_content' => new Content(),
            '_controller' => 'service:indexAction',
        );

        $this->assertEquals($expectedDefaults, $actualDefaults);
    }
}
