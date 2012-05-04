<?php

namespace Symfony\Cmf\Component\Routing\Tests\Routing;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Cmf\Component\Routing\RouteRepositoryInterface;
use Symfony\Cmf\Component\Routing\Document\Route;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;
use Symfony\Cmf\Component\Routing\DoctrineRouter;

class DoctrineRouterTest extends CmfUnitTestCase
{
    protected $contentDocument;
    protected $routeDocument;
    protected $loader;
    protected $repository;
    protected $resolver;
    protected $router;
    protected $context;


    public function setUp()
    {
        $this->contentDocument = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\RouteAwareInterface');
        $this->routeDocument = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Document\\Route', array('getDefaults', 'getRouteContent', 'getUrl'));
        $this->loader = $this->buildMock("Symfony\\Component\\Config\\Loader\\LoaderInterface");
        $this->repository = $this->buildMock("Symfony\\Cmf\\Component\\Routing\\RouteRepositoryInterface", array('findManyByUrl'));

        $this->resolver = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Resolver\\ControllerResolverInterface', array('getController'));

        $this->context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');

        $this->router = new DoctrineRouter($this->repository);
        $this->router->addControllerResolver($this->resolver);
    }

    /**
     * rather trivial, but we want 100% coverage
     */
    public function testContext()
    {
        $this->router->setContext($this->context);
        $this->assertSame($this->context, $this->router->getContext());
    }

    public function testRouteCollection()
    {
        $collection = $this->router->getRouteCollection();
        $this->assertInstanceOf('Symfony\\Component\\Routing\\RouteCollection', $collection);
        // TODO: once this is implemented, check content of collection
    }


    /// generator tests ///

    public function testGetGenerator()
    {
        $router = new DoctrineRouter($this->repository);
        $router->setContext($this->context);
        $generator = $router->getGenerator(new \Symfony\Component\Routing\RouteCollection());
        $this->assertInstanceOf('Symfony\Component\Routing\Generator\UrlGeneratorInterface', $generator);
    }

    public function testGenerate()
    {
        $route = new RouteMock();
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($route)));

        $generator = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGenerator')->disableOriginalConstructor()->getMock();
        $generator->expects($this->once())
            ->method('generate')
            ->with(DoctrineRouter::ROUTE_NAME_PREFIX, array(), false)
            ->will($this->returnValue('/base/test/route'));

        $router = new TestRouter($this->repository, null, $generator, $route);
        $router->setContext($this->context);

        $url = $router->generate('ignore', array('content'=>$this->contentDocument));
        $this->assertEquals('/base/test/route', $url);
    }

    public function testGenerateEmptyRouteString()
    {
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array(new RouteMock('/'))));

        $generator = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGenerator')->disableOriginalConstructor()->getMock();
        $generator->expects($this->once())
            ->method('generate')
            ->with(DoctrineRouter::ROUTE_NAME_PREFIX, array(), false)
            ->will($this->returnValue('/'));

        $router = new TestRouter($this->repository, null, $generator);
        $router->setContext($this->context);

        $url = $router->generate('ignore', array('content'=>$this->contentDocument, 'route' => ''));
        $this->assertEquals('/', $url);
    }

    public function testGenerateAbsolute()
    {
        $content = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\RouteAwareInterface');
        $content->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array(new RouteMock())));


        $generator = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGenerator')->disableOriginalConstructor()->getMock();
        $generator->expects($this->once())
            ->method('generate')
            ->with(DoctrineRouter::ROUTE_NAME_PREFIX, array(), true)
            ->will($this->returnValue('http://test.domain/base/test/route'));

        $router = new TestRouter($this->repository, null, $generator);
        $router->setContext($this->context);

        $url = $router->generate('ignore', array('content'=>$content), true);
        $this->assertEquals('http://test.domain/base/test/route', $url);
    }

    public function testGenerateFromRoute()
    {
        $generator = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGenerator')->disableOriginalConstructor()->getMock();
        $generator->expects($this->once())
            ->method('generate')
            ->with(DoctrineRouter::ROUTE_NAME_PREFIX, array(), false)
            ->will($this->returnValue('/base/test/route'));

        $router = new TestRouter($this->repository, null, $generator);
        $router->setContext($this->context);


        $url = $router->generate('ignore', array('route'=>new RouteMock()));
        $this->assertEquals('/base/test/route', $url);
    }

    public function testGenerateMultilang()
    {
        $route_en = new RouteMock('/en', 'en');
        $route_de = new RouteMock('/de', 'de');
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($route_en, $route_de)));

        $generator = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGenerator')->disableOriginalConstructor()->getMock();
        $generator->expects($this->once())
            ->method('generate')
            ->with(DoctrineRouter::ROUTE_NAME_PREFIX, array('_locale' => 'de'), false)
            ->will($this->returnValue('/base/de'));

        $router = new TestRouter($this->repository, null, $generator, $route_de);
        $router->setContext($this->context);

        $url = $router->generate('ignore', array('content'=>$this->contentDocument, '_locale' => 'de'));
        $this->assertEquals('/base/de', $url);
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateNoContent()
    {
        $this->router->generate('ignore', array());
    }
    /**
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateInvalidContent()
    {
        $this->router->generate('ignore', array('content' => $this));
    }
    /**
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateNoRoutes()
    {
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array()));

        $this->router->generate('ignore', array('content'=>$this->contentDocument));
    }
    /**
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateInvalidRoute()
    {
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($this)));

        $this->router->generate('ignore', array('content'=>$this->contentDocument));
    }


    /// match tests ///

    public function testGetMatcher()
    {
        $router = new DoctrineRouter($this->repository);
        $router->setContext($this->context);
        $matcher = $router->getMatcher(new \Symfony\Component\Routing\RouteCollection());
        $this->assertInstanceOf('Symfony\Component\Routing\Matcher\UrlMatcherInterface', $matcher);
    }

    public function testMatch()
    {
        $url_alias = "/company/more";

        $this->routeDocument->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue($this->contentDocument));

        $this->repository->expects($this->once())
                ->method('findManyByUrl')
                ->with($url_alias)
                ->will($this->returnValue(array($url_alias => $this->routeDocument)));

        $this->resolver->expects($this->once())
                ->method('getController')
                ->will($this->returnValue('NameSpace\\Controller::action'));

        $matcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcher')->disableOriginalConstructor()->getMock();
        $matcher->expects($this->once())
            ->method('match')
            ->with($url_alias)
            ->will($this->returnValue(array('_route' => DoctrineRouter::ROUTE_NAME_PREFIX.'_company_more')));

        $router = new TestRouter($this->repository, $matcher);
        $router->setContext($this->context);
        $router->addControllerResolver($this->resolver);

        $results = $router->match($url_alias);

        $expected = array(
            '_controller' => 'NameSpace\\Controller::action',
            '_route' => DoctrineRouter::ROUTE_NAME_PREFIX.'_company_more',
            'path' => $url_alias,
            '_content' => $this->contentDocument,
        );

        $this->assertEquals($expected, $results);
    }

    public function testNoReferenceMatch()
    {
        $url_alias = "/company/more_no_reference";

        $this->routeDocument->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue(null));

        $this->resolver->expects($this->once())
                ->method('getController')
                ->with($this->routeDocument)
                ->will($this->returnValue('NameSpace\\Controller::action'));

        $this->repository->expects($this->once())
                ->method('findManyByUrl')
                ->with($url_alias)
                ->will($this->returnValue(array($url_alias => $this->routeDocument)));

        $matcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcher')->disableOriginalConstructor()->getMock();
        $matcher->expects($this->once())
            ->method('match')
            ->with($url_alias)
            ->will($this->returnValue(array(
                '_route' => DoctrineRouter::ROUTE_NAME_PREFIX.'_company_more_no_reference',
                'type' => 'found'
        )));

        $router = new TestRouter($this->repository, $matcher);
        $router->setContext($this->context);
        $router->addControllerResolver($this->resolver);

        $expected = array(
            '_controller' => 'NameSpace\\Controller::action',
            '_route' => DoctrineRouter::ROUTE_NAME_PREFIX.'_company_more_no_reference',
            'path' => $url_alias,
            'type' => 'found',
        );

        $this->assertEquals($expected, $router->match($url_alias));
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testNoRouteMatch()
    {
        $url_alias = "/company/more_no_match";

        $this->repository->expects($this->once())
                ->method('findManyByUrl')
                ->with($url_alias)
                ->will($this->returnValue(array()));

        $matcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcher')->disableOriginalConstructor()->getMock();
        $matcher->expects($this->once())
            ->method('match')
            ->with($url_alias)
            ->will($this->throwException(new \Symfony\Component\Routing\Exception\ResourceNotFoundException()));

        $router = new TestRouter($this->repository, $matcher);
        $router->setContext($this->context);
        $router->addControllerResolver($this->resolver);

        $router->match($url_alias);
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testNoResolution()
    {
        $url_alias = "/company/more_no_resolution";

        $this->resolver->expects($this->once())
            ->method('getController')
            ->with($this->routeDocument)
            ->will($this->returnValue(false));

        $this->repository->expects($this->once())
            ->method('findManyByUrl')
            ->with($url_alias)
            ->will($this->returnValue(array($url_alias => $this->routeDocument)));

        $matcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcher')->disableOriginalConstructor()->getMock();
        $matcher->expects($this->once())
            ->method('match')
            ->with($url_alias)
            ->will($this->returnValue(array('_route' => DoctrineRouter::ROUTE_NAME_PREFIX.'_company_more_no_resolution')));

        $router = new TestRouter($this->repository, $matcher);
        $router->setContext($this->context);
        $router->addControllerResolver($this->resolver);

        $router->match($url_alias);
    }
}

class RouteMock extends Route implements \Symfony\Cmf\Component\Routing\RouteObjectInterface
{
    private $url;
    private $locale;
    public function __construct($url = '/test/route', $locale = null)
    {
        $this->url = $url;
        $this->locale = $locale;
    }
    public function getUrl()
    {
        return $this->url;
    }
    public function getRouteContent()
    {
        return null;
    }
    public function getDefaults()
    {
        $defaults = array();
        if (! is_null($this->locale)) {
            $defaults['_locale'] = $this->locale;
        }
        return $defaults;
    }
}

/**
 * Extend to return a mock matcher if specified in the constructor
 */
class TestRouter extends DoctrineRouter
{
    private $matcher;
    private $generator;
    private $expectRoute;
    public function __construct(RouteRepositoryInterface $routeRepository, $matcher = null, $generator = null, $expectRoute = null)
    {
        parent::__construct($routeRepository);
        $this->matcher = $matcher;
        $this->generator = $generator;
    }
    public function getMatcher($collection)
    {
        if ($this->matcher) {
            return $this->matcher;
        }
        return parent::getMatcher($collection);
    }
    public function getGenerator($collection)
    {
        if ($this->generator) {
            if ($this->expectRoute) {
                $found = false;
                foreach ($collection as $route) {
                    if ($route === $this->expectRoute) {
                        $found = true;
                    }
                }
                if (! $found) {
                    throw new \Exception('expected route was not in collection');
                }
            }
            return $this->generator;
        }
        return parent::getGenerator($collection);
    }
}

