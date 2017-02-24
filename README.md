# Symfony CMF Routing Component

[![Build Status](https://travis-ci.org/symfony-cmf/routing.svg?branch=master)](https://travis-ci.org/symfony-cmf/routing)
[![Latest Stable Version](https://poser.pugx.org/symfony-cmf/routing/version.png)](https://packagist.org/packages/symfony-cmf/routing)
[![Total Downloads](https://poser.pugx.org/symfony-cmf/routing/d/total.png)](https://packagist.org/packages/symfony-cmf/routing)

The Symfony CMF Routing component extends the Symfony routing component with additional features:

 * A ChainRouter to run several routers in parallel
 * A DynamicRouter that can load routes from any database and can generate
   additional information in the route match.

The CMF Routing component does not need the Symfony full stack framework. It is
also useful in applications not using the full Symfony framework.

For the best integration into the Symfony full stack framework, it is
recommended to use the [RoutingBundle](https://github.com/symfony-cmf/RoutingBundle)
when building Symfony full stack applications.

This library is provided by the [Symfony Content Management Framework (CMF) project](http://cmf.symfony.com/)
and licensed under the [MIT License](LICENSE).


## Requirements

* PHP 5.6 / 7
* The Symfony Routing component (2.8 - 3.\*)
* See also the `require` section of [composer.json](composer.json)


## Documentation

For the install guide and reference, see:

* [Routing component documentation](http://symfony.com/doc/master/cmf/components/routing/index.html)

See also:

* [All Symfony CMF documentation](http://symfony.com/doc/master/cmf/index.html) - complete Symfony CMF reference
* [Symfony CMF Website](http://cmf.symfony.com/) - introduction, live demo, support and community links


## Contributing

Pull requests are welcome. Please see our
[CONTRIBUTING](https://github.com/symfony-cmf/symfony-cmf/blob/master/CONTRIBUTING.md)
guide.

Unit and/or functional tests exist for this component. See the
[Testing documentation](http://symfony.com/doc/master/cmf/components/testing.html)
for a guide to running the tests.

Thanks to
[everyone who has contributed](https://github.com/symfony-cmf/Routing/contributors) already.
