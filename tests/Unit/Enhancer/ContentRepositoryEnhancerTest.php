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
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Symfony\Cmf\Component\Routing\Enhancer\ContentRepositoryEnhancer;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;

class ContentRepositoryEnhancerTest extends TestCase
{
    private ContentRepositoryEnhancer $mapper;
    private Request $request;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $cRepository = $this->createMock(ContentRepositoryInterface::class);
        $cRepository
            ->method('findById')
            ->willReturn('document')
        ;
        $this->mapper = new ContentRepositoryEnhancer($cRepository);

        $this->request = Request::create('/test');
    }

    /**
     * @dataProvider dataEnhancer
     */
    public function testEnhancer(array $defaults, array $expected): void
    {
        $this->assertEquals($expected, $this->mapper->enhance($defaults, $this->request));
    }

    public function dataEnhancer(): array
    {
        return [
            'empty' => [[], []],
            'with content_id' => [
                [
                    RouteObjectInterface::CONTENT_ID => 'Simple:1',
                ],
                [
                    RouteObjectInterface::CONTENT_ID => 'Simple:1',
                    RouteObjectInterface::CONTENT_OBJECT => 'document',
                ],
            ],
            'with content_id and content' => [
                [
                    RouteObjectInterface::CONTENT_ID => 'Simple:1',
                    RouteObjectInterface::CONTENT_OBJECT => 'exist object',
                ],
                [
                    RouteObjectInterface::CONTENT_ID => 'Simple:1',
                    RouteObjectInterface::CONTENT_OBJECT => 'exist object',
                ],
            ],
            'with content' => [
                [
                    RouteObjectInterface::CONTENT_OBJECT => 'exist object',
                ],
                [
                    RouteObjectInterface::CONTENT_OBJECT => 'exist object',
                ],
            ],
        ];
    }
}
