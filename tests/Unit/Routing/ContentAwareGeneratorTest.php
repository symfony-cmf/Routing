<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Routing;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Component\Routing\ContentAwareGenerator;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Cmf\Component\Routing\Tests\Unit\Routing\RouteMock;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;

class ContentAwareGeneratorTest extends TestCase
{
    /**
     * @var RouteReferrersReadInterface|MockObject
     */
    private $contentDocument;

    /**
     * @var RouteMock|MockObject
     */
    private $routeDocument;

    /**
     * @var CompiledRoute|MockObject
     */
    private $routeCompiled;

    /**
     * @var RouteProviderInterface|MockObject
     */
    private $provider;

    /**
     * @var TestableContentAwareGenerator
     */
    private $generator;

    /**
     * @var RequestContext|MockObject
     */
    private $context;

    public function setUp()
    {
        $this->contentDocument = $this->createMock(RouteReferrersReadInterface::class);
        $this->routeDocument = $this->getMockBuilder(RouteMock::class)
            ->disableOriginalConstructor()
            ->setMethods(['compile', 'getContent'])
            ->getMock();

        $this->routeCompiled = $this->createMock(CompiledRoute::class);
        $this->provider = $this->createMock(RouteProviderInterface::class);
        $this->context = $this->createMock(RequestContext::class);

        $this->generator = new TestableContentAwareGenerator($this->provider);
    }

    public function testGenerateFromContent()
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([$this->routeDocument]))
        ;
        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate($this->contentDocument));
    }

    public function testGenerateFromContentId()
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;

        $contentRepository = $this->createMock(ContentRepositoryInterface::class);
        $contentRepository->expects($this->once())
            ->method('findById')
            ->with('/content/id')
            ->will($this->returnValue($this->contentDocument))
        ;
        $this->generator->setContentRepository($contentRepository);

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([$this->routeDocument]))
        ;

        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate('', ['content_id' => '/content/id']));
    }

    public function testGenerateEmptyRouteString()
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([$this->routeDocument]))
        ;

        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate($this->contentDocument));
    }

    public function testGenerateRouteMultilang()
    {
        /** @var RouteMock $route_en */
        $route_en = $this->getMockBuilder(RouteMock::class)
            ->disableOriginalConstructor()
            ->setMethods(['compile', 'getContent'])
            ->getMock();
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([$route_en, $route_de]))
        ;
        $route_en->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->contentDocument))
        ;
        $route_en->expects($this->never())
            ->method('compile')
        ;
        $route_de->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate($route_en, ['_locale' => 'de']));
    }

    public function testGenerateRouteMultilangDefaultLocale()
    {
        $route = $this->createMock(RouteMock::class);
        $route->expects($this->any())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;
        $route->expects($this->any())
            ->method('getRequirement')
            ->with('_locale')
            ->will($this->returnValue('de|en'))
        ;
        $route->expects($this->any())
            ->method('getDefault')
            ->with('_locale')
            ->will($this->returnValue('en'))
        ;
        $this->routeCompiled->expects($this->any())
            ->method('getVariables')
            ->will($this->returnValue([]))
        ;

        $this->assertEquals('result_url', $this->generator->generate($route, ['_locale' => 'en']));
    }

    public function testGenerateRouteMultilangLocaleNomatch()
    {
        $route_en = $this->getMockBuilder(RouteMock::class)
            ->disableOriginalConstructor()
            ->setMethods(['compile', 'getContent'])
            ->getMock();
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([$route_en, $route_de]))
        ;
        $route_en->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->contentDocument))
        ;
        $route_en->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;
        $route_de->expects($this->never())
            ->method('compile')
        ;

        $this->assertEquals('result_url', $this->generator->generate($route_en, ['_locale' => 'fr']));
    }

    public function testGenerateNoncmfRouteMultilang()
    {
        $route_en = $this->createMock(Route::class);

        $route_en->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate($route_en, ['_locale' => 'de']));
    }

    public function testGenerateRoutenameMultilang()
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
            ->will($this->returnValue($route_en))
        ;
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([$route_en, $route_de]))
        ;
        $route_en->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->contentDocument))
        ;
        $route_en->expects($this->never())
            ->method('compile')
        ;
        $route_de->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate($name, ['_locale' => 'de']));
    }

    public function testGenerateRoutenameMultilangNotFound()
    {
        $name = 'foo/bar';

        $this->provider->expects($this->once())
            ->method('getRouteByName')
            ->with($name)
            ->will($this->returnValue(null))
        ;

        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate($name, ['_locale' => 'de']);
    }

    public function testGenerateDocumentMultilang()
    {
        $route_en = $this->getMockBuilder(RouteMock::class)
            ->disableOriginalConstructor()
            ->setMethods(['compile', 'getContent'])
            ->getMock();
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([$route_en, $route_de]))
        ;
        $route_en->expects($this->never())
            ->method('compile')
        ;
        $route_de->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate($this->contentDocument, ['_locale' => 'de']));
    }

    public function testGenerateDocumentMultilangLocaleNomatch()
    {
        $route_en = $this->createMock(RouteMock::class);
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([$route_en, $route_de]))
        ;
        $route_en->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;
        $route_de->expects($this->never())
            ->method('compile')
        ;

        $this->assertEquals('result_url', $this->generator->generate($this->contentDocument, ['_locale' => 'fr']));
    }

    /**
     * Generate without any information.
     */
    public function testGenerateNoContent()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate('', []);
    }

    /**
     * Generate with an object that is neither a route nor route aware.
     */
    public function testGenerateInvalidContent()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate($this);
    }

    /**
     * Generate with a content_id but there is no content repository.
     */
    public function testGenerateNoContentRepository()
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
    public function testGenerateNoContentFoundInRepository()
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;

        $contentRepository = $this->createMock(ContentRepositoryInterface::class);
        $contentRepository->expects($this->once())
            ->method('findById')
            ->with('/content/id')
            ->will($this->returnValue(null))
        ;
        $this->generator->setContentRepository($contentRepository);

        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate('', ['content_id' => '/content/id']);
    }

    /**
     * Generate with content_id but the object at id is not route aware.
     */
    public function testGenerateWrongContentClassInRepository()
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;

        $contentRepository = $this->createMock(ContentRepositoryInterface::class);
        $contentRepository->expects($this->once())
            ->method('findById')
            ->with('/content/id')
            ->will($this->returnValue($this))
        ;
        $this->generator->setContentRepository($contentRepository);

        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate('', ['content_id' => '/content/id']);
    }

    /**
     * Generate from a content that has no routes associated.
     */
    public function testGenerateNoRoutes()
    {
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([]));

        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate($this->contentDocument);
    }

    /**
     * Generate from a content that returns something that is not a route as route.
     */
    public function testGenerateInvalidRoute()
    {
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue([$this]));

        $this->expectException(RouteNotFoundException::class);
        $this->generator->generate($this->contentDocument);
    }

    public function testGetLocaleAttribute()
    {
        $this->generator->setDefaultLocale('en');

        $attributes = ['_locale' => 'fr'];
        $this->assertEquals('fr', $this->generator->getLocale($attributes));
    }

    public function testGetLocaleDefault()
    {
        $this->generator->setDefaultLocale('en');

        $attributes = [];
        $this->assertEquals('en', $this->generator->getLocale($attributes));
    }

    public function testGetLocaleContext()
    {
        $this->generator->setDefaultLocale('en');

        $this->generator->getContext()->setParameter('_locale', 'de');

        $attributes = [];
        $this->assertEquals('de', $this->generator->getLocale($attributes));
    }

    public function testSupports()
    {
        $this->assertTrue($this->generator->supports(''));
        $this->assertTrue($this->generator->supports(null));
        $this->assertTrue($this->generator->supports($this->contentDocument));
        $this->assertFalse($this->generator->supports($this));
    }

    public function testGetRouteDebugMessage()
    {
        $this->assertContains('/some/content', $this->generator->getRouteDebugMessage(null, ['content_id' => '/some/content']));
        $this->assertContains('Route aware content Symfony\Cmf\Component\Routing\Tests\Routing\RouteAware', $this->generator->getRouteDebugMessage(new RouteAware()));
        $this->assertContains('/some/content', $this->generator->getRouteDebugMessage('/some/content'));
    }
}

/**
 * Overwrite doGenerate to reduce amount of mocking needed.
 */
class TestableContentAwareGenerator extends ContentAwareGenerator
{
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = [])
    {
        return 'result_url';
    }

    // expose as public

    public function getLocale($parameters)
    {
        return parent::getLocale($parameters);
    }
}

class RouteAware implements RouteReferrersReadInterface
{
    public function getRoutes()
    {
        return [];
    }
}
