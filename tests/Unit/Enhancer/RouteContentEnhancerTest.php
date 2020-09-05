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
use Symfony\Cmf\Component\Routing\Enhancer\RouteContentEnhancer;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class RouteContentEnhancerTest extends TestCase
{
    /**
     * @var RouteContentEnhancer
     */
    private $mapper;

    private $document;

    private $request;

    public function setUp(): void
    {
        $this->document = $this->createMock(RouteObject::class);

        $this->mapper = new RouteContentEnhancer(RouteObjectInterface::ROUTE_OBJECT, '_content');

        $this->request = Request::create('/test');
    }

    public function testContent()
    {
        $targetDocument = new TargetDocument();
        $this->document->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($targetDocument));

        $defaults = [RouteObjectInterface::ROUTE_OBJECT => $this->document];
        $expected = [RouteObjectInterface::ROUTE_OBJECT => $this->document, '_content' => $targetDocument];

        $this->assertEquals($expected, $this->mapper->enhance($defaults, $this->request));
    }

    public function testFieldAlreadyThere()
    {
        $this->document->expects($this->never())
            ->method('getContent')
        ;

        $defaults = [RouteObjectInterface::ROUTE_OBJECT => $this->document, '_content' => 'foo'];

        $this->assertEquals($defaults, $this->mapper->enhance($defaults, $this->request));
    }

    public function testNoContent()
    {
        $this->document->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(null));

        $defaults = [RouteObjectInterface::ROUTE_OBJECT => $this->document];
        $this->assertEquals($defaults, $this->mapper->enhance($defaults, $this->request));
    }

    public function testNoCmfRoute()
    {
        $defaults = [RouteObjectInterface::ROUTE_OBJECT => $this->createMock(Route::class)];
        $this->assertEquals($defaults, $this->mapper->enhance($defaults, $this->request));
    }
}

class TargetDocument
{
}

class UnknownDocument
{
}
