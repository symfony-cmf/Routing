<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Unit\Routing;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Component\Routing\ContentAwareGenerator;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;

class ContentAwareGeneratorTest extends TestCase
{
    /**
     * @var RouteReferrersReadInterface&MockObject
     */
    private $contentDocument;

    /**
     * @var RouteMock&MockObject
     */
    private $routeDocument;

    /**
     * @var CompiledRoute
     */
    private $routeCompiled;

    /**
     * @var RouteProviderInterface&MockObject
     */
    private $provider;

    /**
     * @var TestableContentAwareGenerator
     */
    private $generator;

    public function setUp(): void
    {
        $this->contentDocument = $this->createMock(RouteReferrersReadInterface::class);
        $this->routeDocument = $this->getMockBuilder(RouteMock::class)
            ->disableOriginalConstructor()
            ->setMethods(['compile', 'getContent'])
            ->getMock();

        $this->routeCompiled = new CompiledRoute('', '', [], []);
        $this->provider = $this->createMock(RouteProviderInterface::class);

        $this->generator = new TestableContentAwareGenerator($this->provider);
    }

    public function testGenerateFromContentInParameters(): void
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;
        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;

        $this->assertEquals('result_url', $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [RouteObjectInterface::ROUTE_OBJECT => $this->routeDocument]));
    }

    public function testGenerateFromContentIdEmptyRouteName(): void
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;

        $contentRepository = $this->createMock(ContentRepositoryInterface::class);
        $contentRepository->expects($this->once())
            ->method('findById')
            ->with('/content/id')
            ->willReturn($this->contentDocument)
        ;
        $this->generator->setContentRepository($contentRepository);

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->willReturn([$this->routeDocument])
        ;

        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;

        $this->assertEquals('result_url', $this->generator->generate('', ['content_id' => '/content/id']));
    }

    public function testGenerateFromContentId(): void
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;

        $contentRepository = $this->createMock(ContentRepositoryInterface::class);
        $contentRepository->expects($this->once())
            ->method('findById')
            ->with('/content/id')
            ->willReturn($this->contentDocument)
        ;
        $this->generator->setContentRepository($contentRepository);

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->willReturn([$this->routeDocument])
        ;

        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;

        $generated = $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, ['content_id' => '/content/id']);
        $this->assertEquals('result_url', $generated);
    }

    public function testGenerateEmptyRouteString(): void
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->willReturn([$this->routeDocument])
        ;

        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;

        $generated = $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [RouteObjectInterface::ROUTE_OBJECT => $this->contentDocument]);
        $this->assertEquals('result_url', $generated);
    }

    public function testGenerateRouteMultilang(): void
    {
        /** @var RouteMock&MockObject $route_en */
        $route_en = $this->getMockBuilder(RouteMock::class)
            ->disableOriginalConstructor()
            ->setMethods(['compile', 'getContent'])
            ->getMock();
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->willReturn([$route_en, $route_de])
        ;
        $route_en->expects($this->once())
            ->method('getContent')
            ->willReturn($this->contentDocument)
        ;
        $route_en->expects($this->never())
            ->method('compile')
        ;
        $route_de->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;

        $generated = $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, ['_locale' => 'de', RouteObjectInterface::ROUTE_OBJECT => $route_en]);
        $this->assertEquals('result_url', $generated);
    }

    public function testGenerateRouteMultilangDefaultLocale(): void
    {
        $route = $this->createMock(RouteMock::class);
        $route
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;
        $route
            ->method('getRequirement')
            ->with('_locale')
            ->willReturn('de|en')
        ;
        $route
            ->method('getDefault')
            ->with('_locale')
            ->willReturn('en')
        ;

        $generated = $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, ['_locale' => 'en', RouteObjectInterface::ROUTE_OBJECT => $route]);
        $this->assertEquals('result_url', $generated);
    }

    public function testGenerateRouteMultilangLocaleNomatch(): void
    {
        /** @var RouteMock&MockObject $route_en */
        $route_en = $this->getMockBuilder(RouteMock::class)
            ->disableOriginalConstructor()
            ->setMethods(['compile', 'getContent'])
            ->getMock();
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->willReturn([$route_en, $route_de])
        ;
        $route_en->expects($this->once())
            ->method('getContent')
            ->willReturn($this->contentDocument)
        ;
        $route_en->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;
        $route_de->expects($this->never())
            ->method('compile')
        ;

        $generated = $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, ['_locale' => 'fr', RouteObjectInterface::ROUTE_OBJECT => $route_en]);
        $this->assertEquals('result_url', $generated);
    }

    public function testGenerateNoncmfRouteMultilang(): void
    {
        $route_en = $this->createMock(Route::class);

        $route_en->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;

        $generated = $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, ['_locale' => 'de', RouteObjectInterface::ROUTE_OBJECT => $route_en]);
        $this->assertEquals('result_url', $generated);
    }

    public function testGenerateRoutenameMultilang(): void
    {
        $name = 'foo/bar';
        $route_en = $this->getMockBuilder(RouteMock::class)
            ->disableOriginalConstructor()
            ->setMethods(['compile', 'getContent'])
            ->getMock();
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->provider->expects($this->once())
            ->method('getRouteByName')
            ->with($name)
            ->willReturn($route_en)
        ;
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->willReturn([$route_en, $route_de])
        ;
        $route_en->expects($this->once())
            ->method('getContent')
            ->willReturn($this->contentDocument)
        ;
        $route_en->expects($this->never())
            ->method('compile')
        ;
        $route_de->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;

        $this->assertEquals('result_url', $this->generator->generate($name, ['_locale' => 'de']));
    }

    public function testGenerateDocumentMultilang(): void
    {
        /** @var RouteMock&MockObject $route_en */
        $route_en = $this->getMockBuilder(RouteMock::class)
            ->disableOriginalConstructor()
            ->setMethods(['compile', 'getContent'])
            ->getMock();
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->willReturn([$route_en, $route_de])
        ;
        $route_en->expects($this->never())
            ->method('compile')
        ;
        $route_de->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;

        $generated = $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, ['_locale' => 'de', RouteObjectInterface::ROUTE_OBJECT => $this->contentDocument]);
        $this->assertEquals('result_url', $generated);
    }

    public function testGenerateDocumentMultilangLocaleNomatch(): void
    {
        $route_en = $this->createMock(RouteMock::class);
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->willReturn([$route_en, $route_de])
        ;
        $route_en->expects($this->once())
            ->method('compile')
            ->willReturn($this->routeCompiled)
        ;
        $route_de->expects($this->never())
            ->method('compile')
        ;

        $generated = $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, ['_locale' => 'fr', RouteObjectInterface::ROUTE_OBJECT => $this->contentDocument]);
        $this->assertEquals('result_url', $generated);
    }

    /**
     * Generate without any information.
     */
    public function testGenerateNoContent(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate('', []);
    }

    /**
     * Generate with an object that is neither a route nor route aware.
     */
    public function testGenerateInvalidContent(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [RouteObjectInterface::ROUTE_OBJECT => $this]);
    }

    /**
     * Generate with a content_id but there is no content repository.
     */
    public function testGenerateNoContentRepository(): void
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;

        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate('', ['content_id' => '/content/id']);
    }

    /**
     * Generate with content_id but the content is not found.
     */
    public function testGenerateNoContentFoundInRepository(): void
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;

        $contentRepository = $this->createMock(ContentRepositoryInterface::class);
        $contentRepository->expects($this->once())
            ->method('findById')
            ->with('/content/id')
            ->willReturn(null)
        ;
        $this->generator->setContentRepository($contentRepository);

        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate('', ['content_id' => '/content/id']);
    }

    /**
     * Generate with content_id but the object at id is not route aware.
     */
    public function testGenerateWrongContentClassInRepository(): void
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;

        $contentRepository = $this->createMock(ContentRepositoryInterface::class);
        $contentRepository->expects($this->once())
            ->method('findById')
            ->with('/content/id')
            ->willReturn($this)
        ;
        $this->generator->setContentRepository($contentRepository);

        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate('', ['content_id' => '/content/id']);
    }

    /**
     * Generate from a content that has no routes associated.
     */
    public function testGenerateNoRoutes(): void
    {
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->willReturn([]);

        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [RouteObjectInterface::ROUTE_OBJECT => $this->contentDocument]);
    }

    public function testGetLocaleAttribute(): void
    {
        $this->generator->setDefaultLocale('en');

        $attributes = ['_locale' => 'fr'];
        $this->assertEquals('fr', $this->generator->getLocale($attributes));
    }

    public function testGetLocaleDefault(): void
    {
        $this->generator->setDefaultLocale('en');

        $attributes = [];
        $this->assertEquals('en', $this->generator->getLocale($attributes));
    }

    public function testGetLocaleContext(): void
    {
        $this->generator->setDefaultLocale('en');

        $this->generator->getContext()->setParameter('_locale', 'de');

        $attributes = [];
        $this->assertEquals('de', $this->generator->getLocale($attributes));
    }

    public function testGetRouteDebugMessage(): void
    {
        $this->assertStringContainsString('/some/content', $this->generator->getRouteDebugMessage(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, ['content_id' => '/some/content']));
        $this->assertStringContainsString('Route aware content Symfony\Cmf\Component\Routing\Tests\Unit\Routing\RouteAware', $this->generator->getRouteDebugMessage(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [RouteObjectInterface::ROUTE_OBJECT => new RouteAware()]));
        $this->assertStringContainsString('/some/content', $this->generator->getRouteDebugMessage('/some/content'));
    }
}

/**
 * Overwrite doGenerate to reduce amount of mocking needed.
 */
class TestableContentAwareGenerator extends ContentAwareGenerator
{
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = []): string
    {
        return 'result_url';
    }

    // expose as public

    public function getLocale(array $parameters): ?string
    {
        return parent::getLocale($parameters);
    }
}

class RouteAware implements RouteReferrersReadInterface
{
    public function getRoutes(): iterable
    {
        return [];
    }
}
