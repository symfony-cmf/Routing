Changelog
=========

* **2014-09-05**: Dropped DynamicRouter::match and DynamicRouter no longer
  implements RouterInterface but only RequestMatcherInterface and
  UrlGeneratorInterface. The match method is redundant with matchRequest but
  there was a potential information loss by re-creating the request. If you use
  DynamicRouter directly, get access to the Request object or if you are
  stand-alone create the request with Request::createFromGlobals().
  Deprecated ChainedRouterInterface as it adds no additional information over
  VersatileGeneratorInterface.

1.3.0-RC1
---------

* **2014-08-20**: Added an interface for the ChainRouter
* **2014-06-06**: Updated to PSR-4 autoloading

1.2.0
-----

Release 1.2.0

1.2.0-RC1
---------

* **2013-12-23**: add support for ChainRouter::getRouteCollection()
* **2013-01-07**: Removed the deprecated $parameters argument in
  RouteProviderInterface::getRouteByName and getRoutesByNames.

1.1.0
-----

Release 1.1.0

1.1.0-RC1
---------

* **2013-07-31**: DynamicRouter now accepts an EventDispatcher to trigger a
  RouteMatchEvent right before the matching starts
* **2013-07-29**: Renamed RouteAwareInterface to RouteReferrersReadInterface
  for naming consistency and added RouteReferrersInterface for write access.
* **2013-07-13**: NestedMatcher now expects a FinalMatcherInterface as second
  argument of the constructor

1.1.0-alpha1
------------

* **2013-04-30**: Dropped Symfony 2.1 support and got rid of
  ConfigurableUrlMatcher class
* **2013-04-05**: [ContentAwareGenerator] Fix locale handling to always respect
  locale but never have unnecessary ?locale=
