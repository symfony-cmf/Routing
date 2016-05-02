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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;
use Symfony\Cmf\Component\Routing\Enhancer\FieldByClassEnhancer;

class FieldByClassEnhancerTest extends CmfUnitTestCase
{
    private $request;
    /**
     * @var FieldByClassEnhancer
     */
    private $mapper;
    private $document;

    public function setUp()
    {
        $this->document = $this->buildMock('Symfony\Cmf\Component\Routing\Tests\Enhancer\RouteObject');

        $mapping = array('Symfony\Cmf\Component\Routing\Tests\Enhancer\RouteObject' => 'cmf_content.controller:indexAction');

        $this->mapper = new FieldByClassEnhancer('_content', '_controller', $mapping);

        $this->request = Request::create('/test');
    }

    public function testClassFoundInMapping()
    {
        // this is the mock, thus a child class to make sure we properly check with instanceof
        $defaults = array('_content' => $this->document);
        $expected = array(
            '_content' => $this->document,
            '_controller' => 'cmf_content.controller:indexAction',
        );
        $this->assertEquals($expected, $this->mapper->enhance($defaults, $this->request));
    }

    public function testFieldAlreadyThere()
    {
        $defaults = array(
            '_content' => $this->document,
            '_controller' => 'custom.controller:indexAction',
        );
        $this->assertEquals($defaults, $this->mapper->enhance($defaults, $this->request));
    }

    public function testClassNotFoundInMapping()
    {
        $defaults = array('_content' => $this);
        $this->assertEquals($defaults, $this->mapper->enhance($defaults, $this->request));
    }

    public function testNoClass()
    {
        $defaults = array('foo' => 'bar');
        $this->assertEquals($defaults, $this->mapper->enhance($defaults, $this->request));
    }

    /**
     * @dataProvider getMethodDependingDefaults
     */
    public function testHTTPMethodDepending($mapping, $method, $expected)
    {
        $defaults = array(
            '_content' => $this->document,
        );
        $expected['_content'] = $this->document;

        $mapper = new FieldByClassEnhancer('_content', '_controller', $mapping);
        $request = Request::create('/test', $method);
        $this->assertEquals($expected, $mapper->enhance($defaults, $request));
    }

    public function getMethodDependingDefaults()
    {
        return array(
            // old behavior should stay, even for an non-GET request
            array(
                array(
                    'Symfony\Cmf\Component\Routing\Tests\Enhancer\RouteObject' => 'cmf_content.controller:indexAction'
                ),
                Request::METHOD_POST,
                array(
                    '_controller' => 'cmf_content.controller:indexAction'
                ),
            ),
            array(
                array(
                    'Symfony\Cmf\Component\Routing\Tests\Enhancer\RouteObject' => array(
                        array(
                            'methods' => array('put', 'post'),
                            'controller' => 'service:method'
                        ),
                    ),
                ),
                Request::METHOD_POST,
                array(
                    '_controller' => 'service:method'
                ),
            ),
            array(
                array(
                    'Symfony\Cmf\Component\Routing\Tests\Enhancer\RouteObject' => array(
                        array(
                            'methods' => array('put', 'post'),
                            'controller' => 'service:method'
                        ),
                    ),
                ),
                Request::METHOD_PUT,
                array(
                    '_controller' => 'service:method'
                ),
            ),
            array(
                array(
                    'Symfony\Cmf\Component\Routing\Tests\Enhancer\RouteObject' => array(
                        array(
                            'methods' => array('put', 'post'),
                            'controller' => 'service:method'
                        ),
                        array(
                            'methods' => array('any'),
                            'controller' => 'service:readMethod'
                        ),
                    ),
                ),
                Request::METHOD_PUT,
                array(
                    '_controller' => 'service:method'
                ),
            ),
            array(
                array(
                    'Symfony\Cmf\Component\Routing\Tests\Enhancer\RouteObject' => array(
                        array(
                            'methods' => array('put', 'post'),
                            'controller' => 'service:method'
                        ),
                        array(
                            'methods' => array('any'),
                            'controller' => 'service:readMethod'
                        ),
                    ),
                ),
                Request::METHOD_PATCH,
                array(
                    '_controller' => 'service:readMethod'
                ),
            ),
        );
    }
}
