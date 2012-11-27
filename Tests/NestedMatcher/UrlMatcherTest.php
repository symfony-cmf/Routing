<?php

namespace Symfony\Cmf\Component\Routing\Tests\NestedMatcher;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class UrlMatcherTest extends CmfUnitTestCase
{
    protected $routeDocument;
    protected $matcher;
    protected $context;
    protected $request;

    protected $url = '/foo/bar';

    public function setUp()
    {
        $this->routeDocument = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Tests\\Routing\\RouteMock', array('getDefaults'));

        $this->generator = $this->buildMock('Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface', array('supports', 'generate', 'setContext', 'getContext'));
        $this->enhancer = $this->buildMock('Symfony\\Cmf\\Component\\Routing\\Enhancer\\RouteEnhancerInterface', array('enhance'));

        $this->context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');
        $this->request = Request::create($this->url);

        $this->router = new DynamicRouter($this->context, $this->matcher, $this->generator);
        $this->router->addRouteEnhancer($this->enhancer);
    }

    public function testMatchRouteKey()
    {
        $url_alias = "/company/more";

        $this->routeDocument->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue($this->contentDocument));
        $this->routeDocument->expects($this->atLeastOnce())
            ->method('getRouteKey')
            ->will($this->returnValue($url_alias));

        $routeCollection = new RouteCollection();
        $routeCollection->add('_company_more', $this->routeDocument);
        $this->repository->expects($this->once())
            ->method('findManyByUrl')
            ->with($url_alias)
            ->will($this->returnValue($routeCollection));

        $this->mapper->expects($this->once())
            ->method('getController')
            ->will($this->returnValue('NameSpace\\Controller::action'));

        $matcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcher')->disableOriginalConstructor()->getMock();
        $matcher->expects($this->once())
            ->method('match')
            ->with($url_alias)
            ->will($this->returnValue(array('_route' => '_company_more')));

        $router = new TestRouter($this->repository, $matcher);
        $router->setContext($this->context);
        $router->addControllerMapper($this->mapper);

        $results = $router->match($url_alias);

        $expected = array(
            RouteObjectInterface::CONTROLLER_NAME => 'NameSpace\\Controller::action',
            '_route' => $url_alias,
            RouteObjectInterface::CONTENT_OBJECT => $this->contentDocument,
        );

        $this->assertEquals($expected, $results);
    }
}
