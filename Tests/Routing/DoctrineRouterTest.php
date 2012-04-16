<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteRepositoryInterface;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Document\Route;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\DoctrineRouter;

class DoctrineRouterTest extends CmfUnitTestCase
{
    protected $request;
    protected $contentDocument;
    protected $routeDocument;
    protected $loader;
    protected $repository;
    protected $resolver;
    protected $router;
    protected $attributes;
    protected $container;
    protected $context;


    public function setUp()
    {
        $this->contentDocument = $this->buildMock('Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Routing\\RouteAwareInterface');
        $this->routeDocument = $this->buildMock('Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Document\\Route', array('getDefaults', 'getRouteContent', 'getUrl'));
        $this->loader = $this->buildMock("Symfony\\Component\\Config\\Loader\\LoaderInterface");
        $this->repository = $this->buildMock("Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Routing\\RouteRepositoryInterface", array('findManyByUrl'));

        $this->resolver = $this->buildMock('Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Resolver\\ControllerResolverInterface', array('getController'));

        $this->attributes = $this->buildMock('Symfony\\Component\\HttpFoundation\\ParameterBag');
        $this->request = new RequestMock($this->attributes);
        $this->container = $this->buildMock('Symfony\\Component\\DependencyInjection\\ContainerInterface');
        $this->context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');

        $this->router = new DoctrineRouter($this->container, $this->repository);
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
        $router = new DoctrineRouter($this->container, $this->repository);
        $router->setContext($this->context);
        $generator = $router->getGenerator(new \Symfony\Component\Routing\RouteCollection());
        $this->assertInstanceOf('Symfony\Component\Routing\Generator\UrlGeneratorInterface', $generator);
    }

    public function testGenerate()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request)
        );

        $route = new RouteMock();
        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($route)));

        $generator = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGenerator')->disableOriginalConstructor()->getMock();
        $generator->expects($this->once())
            ->method('generate')
            ->with(DoctrineRouter::ROUTE_NAME_PREFIX, array(), false)
            ->will($this->returnValue('/base/test/route'));

        $router = new TestRouter($this->container, $this->repository, null, $generator, $route);
        $router->setContext($this->context);

        $url = $router->generate('ignore', array('content'=>$this->contentDocument));
        $this->assertEquals('/base/test/route', $url);
    }

    public function testGenerateEmptyRouteString()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request)
        );

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array(new RouteMock('/'))));

        $generator = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGenerator')->disableOriginalConstructor()->getMock();
        $generator->expects($this->once())
            ->method('generate')
            ->with(DoctrineRouter::ROUTE_NAME_PREFIX, array(), false)
            ->will($this->returnValue('/'));

        $router = new TestRouter($this->container, $this->repository, null, $generator);
        $router->setContext($this->context);

        $url = $router->generate('ignore', array('content'=>$this->contentDocument, 'route' => ''));
        $this->assertEquals('/', $url);
    }

    public function testGenerateAbsolute()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request)
        );

        $content = $this->buildMock('Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Routing\\RouteAwareInterface');
        $content->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array(new RouteMock())));


        $generator = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGenerator')->disableOriginalConstructor()->getMock();
        $generator->expects($this->once())
            ->method('generate')
            ->with(DoctrineRouter::ROUTE_NAME_PREFIX, array(), true)
            ->will($this->returnValue('http://test.domain/base/test/route'));

        $router = new TestRouter($this->container, $this->repository, null, $generator);
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

        $router = new TestRouter($this->container, $this->repository, null, $generator);
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

        $router = new TestRouter($this->container, $this->repository, null, $generator, $route_de);
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
        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request)
        );

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array($this)));

        $this->router->generate('ignore', array('content'=>$this->contentDocument));
    }

    public function testGenerateNoRequest()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue(null)
        );

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array(new RouteMock())));

        try {
            $this->router->generate('something', array('content' => $this->contentDocument));
            $this->fail('Expected failure when context is null');
        } catch (\Exception $e) {
            // expected
        }
    }


    /// match tests ///

    public function testGetMatcher()
    {
        $router = new DoctrineRouter($this->container, $this->repository);
        $router->setContext($this->context);
        $matcher = $router->getMatcher(new \Symfony\Component\Routing\RouteCollection());
        $this->assertInstanceOf('Symfony\Component\Routing\Matcher\UrlMatcherInterface', $matcher);
    }

    public function testMatch()
    {
        $url_alias = "/company/more";

        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request)
        );

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

        $this->attributes->expects($this->once())
            ->method('set')
            ->with('contentDocument', $this->contentDocument);

        $matcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcher')->disableOriginalConstructor()->getMock();
        $matcher->expects($this->once())
            ->method('match')
            ->with($url_alias)
            ->will($this->returnValue(array('_route' => 'chain_router_doctrine_route_company_more')));

        $router = new TestRouter($this->container, $this->repository, $matcher);
        $router->setContext($this->context);
        $router->addControllerResolver($this->resolver);

        $results = $router->match($url_alias);

        $expected = array(
            '_controller' => 'NameSpace\\Controller::action',
            '_route' => 'chain_router_doctrine_route_company_more',
            'path' => $url_alias,
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
                '_route' => 'chain_router_doctrine_route_company_more_no_reference',
                'type' => 'found'
        )));

        $router = new TestRouter($this->container, $this->repository, $matcher);
        $router->setContext($this->context);
        $router->addControllerResolver($this->resolver);

        $expected = array(
            '_controller' => 'NameSpace\\Controller::action',
            '_route' => 'chain_router_doctrine_route_company_more_no_reference',
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

        $router = new TestRouter($this->container, $this->repository, $matcher);
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
            ->will($this->returnValue(array('_route' => 'chain_router_doctrine_route_company_more_no_resolution')));

        $router = new TestRouter($this->container, $this->repository, $matcher);
        $router->setContext($this->context);
        $router->addControllerResolver($this->resolver);

        $router->match($url_alias);
    }

    public function testMatchNoRequest()
    {
        $url_alias = '/url';

        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue(null)
        );

        $this->routeDocument->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue($this->contentDocument));

        $this->repository->expects($this->once())
            ->method('findManyByUrl')
            ->with($url_alias)
            ->will($this->returnValue(array($url_alias => $this->routeDocument)));

        $this->resolver->expects($this->once())
            ->method('getController')
            ->with($this->routeDocument)
            ->will($this->returnValue('NameSpace\\Controller::action'));

        $matcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcher')->disableOriginalConstructor()->getMock();
        $matcher->expects($this->once())
            ->method('match')
            ->with($url_alias)
            ->will($this->returnValue(array(
            '_route' => 'chain_router_doctrine_route_url',
            'type' => 'found'
        )));

        $router = new TestRouter($this->container, $this->repository, $matcher);
        $router->setContext($this->context);
        $router->addControllerResolver($this->resolver);

        try {
            $router->match($url_alias);
            $this->fail('Expected failure when context is null');
        } catch (\Exception $e) {
            // expected
        }
    }
}

class RouteMock extends Route implements \Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface
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
    public function __construct(ContainerInterface $container, RouteRepositoryInterface $routeRepository, $matcher = null, $generator = null, $expectRoute = null)
    {
        parent::__construct($container, $routeRepository);
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

class RequestMock extends \Symfony\Component\HttpFoundation\Request
{
    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }
}
