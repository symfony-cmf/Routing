<?php

namespace Symfony\Cmf\Component\Routing\Tests\Routing;

use Symfony\Component\Routing\Route as SymfonyRoute;

use Symfony\Cmf\Component\Routing\ContentAwareGenerator;
use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class ContentAwareGeneratorTest extends CmfUnitTestCase
{
    protected $contentDocument;
    protected $routeDocument;
    protected $routeCompiled;
    protected $provider;

    protected $generator;
    protected $context;

    public function setUp()
    {
        $this->contentDocument = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\RouteAwareInterface');
        $this->routeDocument = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Tests\\Routing\\RouteMock', array('getDefaults', 'compile'));
        $this->routeCompiled = $this->buildMock('Symfony\\Component\\Routing\\CompiledRoute');
        $this->provider = $this->buildMock("Symfony\\Cmf\\Component\\Routing\\RouteProviderInterface");
        $this->context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');

        $this->generator = new TestableContentAwareGenerator($this->provider);
    }


    public function testGenerateFromContent()
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($this->routeDocument)))
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

        $contentRepository = $this->buildMock("Symfony\\Cmf\\Component\\Routing\\ContentRepositoryInterface", array('findById'));
        $contentRepository->expects($this->once())
            ->method('findById')
            ->with('/content/id')
            ->will($this->returnValue($this->contentDocument))
        ;
        $this->generator->setContentRepository($contentRepository);

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($this->routeDocument)))
        ;

        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate('', array('content_id' => '/content/id')));
    }

    public function testGenerateEmptyRouteString()
    {
        $this->provider->expects($this->never())
            ->method('getRouteByName')
        ;

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($this->routeDocument)))
        ;

        $this->routeDocument->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate('', array('content'=>$this->contentDocument)));
    }

    public function testGenerateRouteMultilang()
    {
        $route_en = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Tests\\Routing\\RouteMock', array('getDefaults', 'compile', 'getRouteContent'));
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($route_en, $route_de)))
        ;
        $route_en->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue($this->contentDocument))
        ;
        $route_en->expects($this->never())
            ->method('compile')
        ;
        $route_de->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate($route_en, array('_locale' => 'de')));
    }


    public function testGenerateRouteMultilangNomatch()
    {
        $route_en = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Tests\\Routing\\RouteMock', array('getDefaults', 'compile', 'getRouteContent'));
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($route_en, $route_de)))
        ;
        $route_en->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue($this->contentDocument))
        ;
        $route_en->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;
        $route_de->expects($this->never())
            ->method('compile')
        ;

        $this->assertEquals('result_url', $this->generator->generate($route_en, array('_locale' => 'fr')));
    }

    public function testGenerateNoncmfRouteMultilang()
    {
        $route_en = $this->buildMock('Symfony\\Component\\Routing\\Route', array('getDefaults', 'compile', 'getRouteContent'));

        $route_en->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate($route_en, array('_locale' => 'de')));
    }

    public function testGenerateRoutenameMultilang()
    {
        $name = 'foo/bar';
        $route_en = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Tests\\Routing\\RouteMock', array('getDefaults', 'compile', 'getRouteContent'));
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
            ->will($this->returnValue(array($route_en, $route_de)))
        ;
        $route_en->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue($this->contentDocument))
        ;
        $route_en->expects($this->never())
            ->method('compile')
        ;
        $route_de->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate($name, array('_locale' => 'de')));
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateRoutenameMultilangNotFound()
    {
        $name = 'foo/bar';

        $this->provider->expects($this->once())
            ->method('getRouteByName')
            ->with($name)
            ->will($this->returnValue(null))
        ;

        $this->generator->generate($name, array('_locale' => 'de'));
    }

    public function testGenerateDocumentMultilang()
    {
        $route_en = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Tests\\Routing\\RouteMock', array('getDefaults', 'compile'));
        $route_en->setLocale('en');
        $route_de = $this->routeDocument;
        $route_de->setLocale('de');

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($route_en, $route_de)))
        ;
        $route_en->expects($this->never())
            ->method('compile')
        ;
        $route_de->expects($this->once())
            ->method('compile')
            ->will($this->returnValue($this->routeCompiled))
        ;

        $this->assertEquals('result_url', $this->generator->generate('', array('content'=>$this->contentDocument, '_locale' => 'de')));
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateNoContent()
    {
        $this->generator->generate('', array());
    }
    /**
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateInvalidContent()
    {
        $this->generator->generate('', array('content' => $this));
    }
    /**
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateNoRoutes()
    {
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array()));

        $this->generator->generate('', array('content'=>$this->contentDocument));
    }
    /**
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateInvalidRoute()
    {
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($this)));

        $this->generator->generate('', array('content'=>$this->contentDocument));
    }

    public function testSupports()
    {
        $this->assertTrue($this->generator->supports(''));
        $this->assertTrue($this->generator->supports(null));
        $this->assertTrue($this->generator->supports($this->contentDocument));
        $this->assertFalse($this->generator->supports($this));
    }
}

/**
 * Overwrite doGenerate to reduce amount of mocking needed
 */
class TestableContentAwareGenerator extends ContentAwareGenerator
{
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute, $hostnameTokens = null)
    {
        return 'result_url';
    }
}