Changelog
=========

1.2.0-RC2
---------

* **2014-04-11**: drop Symfony 2.2 compatibility

1.2.0-RC1
---------

* **2013-01-07**: Removed the deprecated $parameters argument in
  RouteProviderInterface::getRouteByName and getRoutesByNames.

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
