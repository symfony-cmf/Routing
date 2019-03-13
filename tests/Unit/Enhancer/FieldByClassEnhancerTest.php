<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Unit\Enhancer;

use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Component\Routing\Enhancer\FieldByClassEnhancer;
use Symfony\Component\HttpFoundation\Request;

class FieldByClassEnhancerTest extends TestCase
{
    private $request;

    /**
     * @var FieldByClassEnhancer
     */
    private $mapper;

    private $document;

    public function setUp()
    {
        $this->document = $this->createMock(RouteObject::class);

        $mapping = [RouteObject::class => 'cmf_content.controller:indexAction'];

        $this->mapper = new FieldByClassEnhancer('_content', '_controller', $mapping);

        $this->request = Request::create('/test');
    }

    public function testClassFoundInMapping()
    {
        // this is the mock, thus a child class to make sure we properly check with instanceof
        $defaults = ['_content' => $this->document];
        $expected = [
            '_content' => $this->document,
            '_controller' => 'cmf_content.controller:indexAction',
        ];
        $this->assertEquals($expected, $this->mapper->enhance($defaults, $this->request));
    }

    public function testFieldAlreadyThere()
    {
        $defaults = [
            '_content' => $this->document,
            '_controller' => 'custom.controller:indexAction',
        ];
        $this->assertEquals($defaults, $this->mapper->enhance($defaults, $this->request));
    }

    public function testClassNotFoundInMapping()
    {
        $defaults = ['_content' => $this];
        $this->assertEquals($defaults, $this->mapper->enhance($defaults, $this->request));
    }

    public function testNoClass()
    {
        $defaults = ['foo' => 'bar'];
        $this->assertEquals($defaults, $this->mapper->enhance($defaults, $this->request));
    }
}
