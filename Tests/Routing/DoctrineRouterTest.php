<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Routing;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\DoctrineRouter;

class DoctrineRouterTest extends CmfUnitTestCase
{
    protected $request;
    protected $contentDocument;
    protected $routeDocument;
    protected $loader;
    protected $registry;
    protected $om;
    protected $resolver;
    protected $router;
    protected $attributes;
    protected $container;

    public function setUp()
    {
        $this->contentDocument = $this->buildMock('Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Routing\\RouteAwareInterface');
        $this->routeDocument = $this->buildMock('Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Routing\\RouteObjectInterface', array('getRouteDefaults', 'getRouteContent', 'getPath'));
        $this->loader = $this->buildMock("Symfony\\Component\\Config\\Loader\\LoaderInterface");
        $this->registry = $this->buildMock('Doctrine\\Common\\Persistence\\ManagerRegistry', array('getManager'));
        $this->om = $this->buildMock("Doctrine\\Common\\Persistence\\ObjectManager", array('find'));

        $this->registry->expects($this->any())
            ->method('getManager')
            ->with(null)
            ->will($this->returnValue($this->om));

        $this->resolver = $this->buildMock('Symfony\\Cmf\\Bundle\\ChainRoutingBundle\\Resolver\\ControllerResolverInterface', array('getController'));

        $this->attributes = $this->buildMock('Symfony\\Component\\HttpFoundation\\ParameterBag');
        $this->request = new RequestMock($this->attributes);
        $this->container = $this->buildMock('Symfony\\Component\\DependencyInjection\\ContainerInterface');

        $this->router = new DoctrineRouter($this->container, $this->registry, null, null, '/idprefix');
        $this->router->setObjectManager($this->om);
        $this->router->addControllerResolver($this->resolver);
    }

    /**
     * rather trivial, but we want 100% coverage
     */
    public function testContext()
    {
        $context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');
        $this->router->setContext($context);
        $this->assertSame($context, $this->router->getContext());
    }

    public function testGenerate()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request)
        );

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array(new RouteMock())));

        $context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');
        $context->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('/base'));
        $this->router->setContext($context);

        $url = $this->router->generate('ignore', array('content'=>$this->contentDocument));
        $this->assertEquals('/base/test/route', $url);
    }

    public function testGenerateHome()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request)
        );

        $this->container->expects($this->any())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request)
        );

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array(new RouteMock('/idprefix'))));

        $context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');
        $context->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('/base'));
        $this->router->setContext($context);

        $url = $this->router->generate('ignore', array('content'=>$this->contentDocument));
        $this->assertEquals('/base/', $url);
    }

    public function testGenerateEmptyRouteString()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request)
        );

        $this->container->expects($this->any())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request)
        );

        $this->contentDocument->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array(new RouteMock('/idprefix'))));

        $context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');
        $context->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('/base'));
        $this->router->setContext($context);

        $url = $this->router->generate('ignore', array('content'=>$this->contentDocument, 'route' => ''));
        $this->assertEquals('/base/', $url);
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

        $context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');
        $context->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('/base'));
        $context->expects($this->once())
            ->method('getScheme')
            ->will($this->returnValue('http'));
        $context->expects($this->once())
            ->method('getHttpPort')
            ->will($this->returnValue(80));
        $context->expects($this->once())
            ->method('getHost')
            ->will($this->returnValue('test.domain'));
        $this->router->setContext($context);

        $url = $this->router->generate('ignore', array('content'=>$content), true);
        $this->assertEquals('http://test.domain/base/test/route', $url);
    }

    public function testGenerateAbsoluteNonstandardHttp()
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

        $context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');
        $context->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('/base'));
        $context->expects($this->once())
            ->method('getScheme')
            ->will($this->returnValue('http'));
        $context->expects($this->any())
            ->method('getHttpPort')
            ->will($this->returnValue(81));
        $context->expects($this->once())
            ->method('getHost')
            ->will($this->returnValue('test.domain'));
        $this->router->setContext($context);

        $url = $this->router->generate('ignore', array('content'=>$content), true);
        $this->assertEquals('http://test.domain:81/base/test/route', $url);
    }

    public function testGenerateAbsoluteNonstandardHttps()
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

        $context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');
        $context->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('/base'));
        $context->expects($this->once())
            ->method('getScheme')
            ->will($this->returnValue('https'));
        $context->expects($this->any())
            ->method('getHttpsPort')
            ->will($this->returnValue(3333));
        $context->expects($this->once())
            ->method('getHost')
            ->will($this->returnValue('test.domain'));
        $this->router->setContext($context);

        $url = $this->router->generate('ignore', array('content'=>$content), true);
        $this->assertEquals('https://test.domain:3333/base/test/route', $url);
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

    public function testRouteCollection()
    {
        $collection = $this->router->getRouteCollection();
        $this->assertInstanceOf('Symfony\\Component\\Routing\\RouteCollection', $collection);
        // TODO: once this is implemented, check content of collection
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
            ->method('getRouteDefaults')
            ->will($this->returnValue(array()));
        $this->routeDocument->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue($this->contentDocument));

        $this->om->expects($this->once())
                ->method('find')
                ->with(null, "/idprefix$url_alias")
                ->will($this->returnValue($this->routeDocument));

        $this->resolver->expects($this->once())
                ->method('getController')
                ->with($this->routeDocument)
                ->will($this->returnValue('NameSpace\\Controller::action'));

        $this->attributes->expects($this->once())
            ->method('set')
            ->with('contentDocument', $this->contentDocument);

        $results = $this->router->match($url_alias);

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
                ->method('getRouteDefaults')
                ->will($this->returnValue(array('type' => 'found')));
        $this->routeDocument->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue(null));

        $this->resolver->expects($this->once())
                ->method('getController')
                ->with($this->routeDocument)
                ->will($this->returnValue('NameSpace\\Controller::action'));

        $this->om->expects($this->once())
                ->method('find')
                ->with(null, "/idprefix$url_alias")
                ->will($this->returnValue($this->routeDocument));

        $expected = array(
            '_controller' => 'NameSpace\\Controller::action',
            '_route' => 'chain_router_doctrine_route_company_more_no_reference',
            'path' => $url_alias,
            'type' => 'found',
        );

        $this->assertEquals($expected, $this->router->match($url_alias));
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testNoRouteMatch()
    {
        $url_alias = "/company/more_no_match";

        $this->om->expects($this->once())
                ->method('find')
                ->with(null, "/idprefix$url_alias")
                ->will($this->returnValue(null));

        $this->router->match($url_alias);
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testNoResolution()
    {
        $url_alias = "/company/more_no_match";

        $this->routeDocument->expects($this->once())
            ->method('getRouteDefaults')
            ->will($this->returnValue(array()));

        $this->resolver->expects($this->once())
            ->method('getController')
            ->with($this->routeDocument)
            ->will($this->returnValue(false));

        $this->om->expects($this->once())
            ->method('find')
            ->with(null, "/idprefix$url_alias")
            ->will($this->returnValue($this->routeDocument));

        $this->router->match($url_alias);
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testExceptionFindingRoute()
    {
        $url_alias = "/company/more_no_match";

        $this->om->expects($this->once())
            ->method('find')
            ->with(null, "/idprefix$url_alias")
            ->will($this->throwException(new \PHPCR\RepositoryException()));

        $this->router->match($url_alias);
    }
}

class RouteMock implements \Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface
{
    private $path;
    public function __construct($path = '/idprefix/test/route')
    {
        $this->path = $path;
    }
    public function getPath()
    {
        return $this->path;
    }
    public function getRouteContent()
    {
        return null;
    }
    public function getRouteDefaults()
    {
        return array();
    }
}
class RequestMock extends \Symfony\Component\HttpFoundation\Request
{
    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }
}
