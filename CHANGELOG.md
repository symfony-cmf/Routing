Changelog
=========

3.0.1
-----

* Bugfix: Fix handling non-array return from route provider (#279)

3.0.0
-----

* [BC Break] Removed deprecated VersatileRouterInterface::supports, as only string route names are
  allowed since Symfony 6.
* [BC Break] As with Symfony itself, the route name now must be a `string`. As noted in
  the changes for CMF Routing 2.3, to generate a route from an object is to use
  `RouteObjectInterface::OBJECT_BASED_ROUTE_NAME` (`cmf_routing_object`) as
  route name and pass the route object in the parameters with key
  `RouteObjectInterface::ROUTE_OBJECT` (`_route_object`).
* [BC Break] Added static type declarations to interfaces and classes.
* Revoked the deprecation on Router::match because Symfony keeps offering the match without request
  object.
* Support Symfony 6, dropped support for older Symfony versions.

2.3.4
-----

* Allow installation with psr/log 2 and 3

2.3.3
-----

* Allow installation with PHP 8.
  (Note that many dependencies do not yet support PHP 8, this release is mainly useful to test PHP 8 support.)

2.3.2
-----

* ProviderBasedGenerator no longer passes the route object in the parameters
  to the Symfony generator, to avoid bogus query strings e.g. from doctrine
  proxy objects.

2.3.1
-----

* ProviderBasedGenerator now also supports the `RouteObjectInterface::OBJECT_BASED_ROUTE_NAME`
  and `RouteObjectInterface::ROUTE_OBJECT`.

2.3.0
-----

* Dropped support for PHP 7.1 and Symfony 3.4 and 4.3.
* Added support for Symfony 5.
* Deprecated passing a route object (or anything else that is not a string) as
  the `$name` parameter in the `generate` method of the ChainRouter and the
  DynamicRouter. Symfony 5 enforces the `$name` parameter to be a string with
  static type declaration.
  The future proof way to generate a route from an object is to use the route
  name `RouteObjectInterface::OBJECT_BASED_ROUTE_NAME` (`cmf_routing_object`)
  and pass the route object in the parameters with key
  `RouteObjectInterface::ROUTE_OBJECT` (`_route_object`).
* The VersatileGeneratorInterface::supports method is deprecated as it was used
  to avoid errors with routers not supporting objects in `$name`.

2.2.0
-----

* Avoid Symfony 4.3 event dispatcher deprecation warnings.

2.1.1
-----

* Fix warning about `get_class` on `null` in PHP > 7.2

2.1.0
-----

* ChainRouter now returns a new RequestContext if none has been set, to be closer in behaviour to the Symfony router.
* Dropped hhvm support

2.0.3
-----

Fixed edge case in error reporting in ChainRouter.

2.0.0
-----

Release 2.0.0

2.0.0-RC3
---------

 * **2017-01-31**: Split out enhancer code from DynamicRouter into RouteEnhancerTrait for reusability.

2.0.0-RC2
---------

Released.

2.0.0-RC1
---------

 * **2016-11-30**: Changed file structure to have all php code in src/

1.4.0
-----

 * **2016-02-27**: Added ContentRepositoryEnhancer that can look up a content by
   ID from a content repository.

1.4.0-RC1
---------

 * **2016-01-09**: When ChainRouter::match is used with a RequestMatcher, the
   Request is now properly rebuilt from the RequestContext if that was set on
   the ChainRouter, and http://localhost is used otherwise to avoid issues with
   paths starting with a double forward slash.
 * **2014-09-29**: ChainRouter does not require a RouterInterface, as a
   RequestMatcher and UrlGenerator is fine too. Fixed chain router interface to
   not force a RouterInterface.
 * **2014-09-29**: Deprecated DynamicRouter::match in favor of matchRequest.

1.3.0
-----

Release 1.3.0

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
