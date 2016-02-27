<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Enhancer;

use Symfony\Cmf\Component\Routing\Enhancer\ContentRepositoryEnhancer;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class ContentRepositoryEnhancerTest extends CmfUnitTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $cRepository = $this->getMock('\Symfony\Cmf\Component\Routing\ContentRepositoryInterface');
        $cRepository
            ->method('findById')
            ->will($this->returnValue('document'))
        ;
        $this->mapper = new ContentRepositoryEnhancer($cRepository);

        $this->request = Request::create('/test');
    }

    /**
     * @dataProvider dataEnhancer
     */
    public function testEnhancer($defaults, $expected)
    {
        $this->assertEquals($expected, $this->mapper->enhance($defaults, $this->request));
    }

    /**
     * @return array
     */
    public function dataEnhancer()
    {
        return array(
            'empty' => array(array(), array()),
            'with content_id' => array(
                array(
                    RouteObjectInterface::CONTENT_ID => 'Simple:1',
                ),
                array(
                    RouteObjectInterface::CONTENT_ID => 'Simple:1',
                    RouteObjectInterface::CONTENT_OBJECT => 'document',
                ),
            ),
            'with content_id and content' => array(
                array(
                    RouteObjectInterface::CONTENT_ID => 'Simple:1',
                    RouteObjectInterface::CONTENT_OBJECT => 'exist object',
                ),
                array(
                    RouteObjectInterface::CONTENT_ID => 'Simple:1',
                    RouteObjectInterface::CONTENT_OBJECT => 'exist object',
                ),
            ),
            'with content' => array(
                array(
                    RouteObjectInterface::CONTENT_OBJECT => 'exist object',
                ),
                array(
                    RouteObjectInterface::CONTENT_OBJECT => 'exist object',
                ),
            ),
        );
    }
}
