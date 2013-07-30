Changelog
=========

1.1
---

* **2013-07-31**: DynamicRouter now accepts an EventDispatcher to trigger a RouteMatchEvent right before the matching starts
* **2013-07-29**: Renamed RouteAwareInterface to RouteReferrersInterface for naming consistency
* **2013-07-13**: NestedMatcher now expects a FinalMatcherInterface as second argument of the constructor
* **2013-04-30**: Dropped Symfony 2.1 support and got rid of ConfigurableUrlMatcher class
* **2013-04-05**: [ContentAwareGenerator] Fix locale handling to always respect locale but never have unnecessary ?locale=
