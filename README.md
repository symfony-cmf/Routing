# Symfony CMF Routing

[![Latest Stable Version](https://poser.pugx.org/symfony-cmf/routing/v/stable)](https://packagist.org/packages/symfony-cmf/routing)
[![Latest Unstable Version](https://poser.pugx.org/symfony-cmf/routing/v/unstable)](https://packagist.org/packages/symfony-cmf/routing)
[![License](https://poser.pugx.org/symfony-cmf/routing/license)](https://packagist.org/packages/symfony-cmf/routing)

[![Total Downloads](https://poser.pugx.org/symfony-cmf/routing/downloads)](https://packagist.org/packages/symfony-cmf/routing)
[![Monthly Downloads](https://poser.pugx.org/symfony-cmf/routing/d/monthly)](https://packagist.org/packages/symfony-cmf/routing)
[![Daily Downloads](https://poser.pugx.org/symfony-cmf/routing/d/daily)](https://packagist.org/packages/symfony-cmf/routing)

Branch | Travis | Coveralls |
------ | ------ | --------- |
master | [![Build Status][travis_unstable_badge]][travis_link] | [![Coverage Status][coveralls_unstable_badge]][coveralls_unstable_link] |

This package is part of the [Symfony Content Management Framework (CMF)](http://cmf.symfony.com/) and licensed
under the [MIT License](LICENSE).

The Symfony CMF Routing component extends the Symfony routing component with additional features:

 * A ChainRouter to run several routers in parallel
 * A DynamicRouter that can load routes from any database and can generate
   additional information in the route match.

The CMF Routing component does not need the Symfony full stack framework. It is
also useful in applications not using the full Symfony framework.

For the best integration into the Symfony full stack framework, it is
recommended to use the [RoutingBundle](https://github.com/symfony-cmf/RoutingBundle)
when building Symfony full stack applications.


## Requirements

* PHP 7.1 / 7.2
* Symfony 2.8 / 3.3 / 3.4 / 4.0
* See also the `require` section of [composer.json](composer.json)

## Documentation

For the install guide and reference, see:

* [symfony-cmf/routing Documentation](http://symfony.com/doc/master/cmf/components/routing/index.html)

See also:

* [All Symfony CMF documentation](http://symfony.com/doc/master/cmf/index.html) - complete Symfony CMF reference
* [Symfony CMF Website](http://cmf.symfony.com/) - introduction, live demo, support and community links

## Support

For general support and questions, please use [StackOverflow](http://stackoverflow.com/questions/tagged/symfony-cmf).

## Contributing

Pull requests are welcome. Please see our
[CONTRIBUTING](https://github.com/symfony-cmf/blob/master/CONTRIBUTING.md)
guide.

Unit and/or functional tests exist for this package. See the
[Testing documentation](http://symfony.com/doc/master/cmf/components/testing.html)
for a guide to running the tests.

Thanks to
[everyone who has contributed](contributors) already.

## License

This package is available under the [MIT license](src/Resources/meta/LICENSE).

[travis_legacy_badge]: https://travis-ci.org/symfony-cmf/routing.svg?branch=master
[travis_stable_badge]: https://travis-ci.org/symfony-cmf/routing.svg?branch=master
[travis_unstable_badge]: https://travis-ci.org/symfony-cmf/routing.svg?branch=master
[travis_link]: https://travis-ci.org/symfony-cmf/routing

[coveralls_legacy_badge]: https://coveralls.io/repos/github/symfony-cmf/routing/badge.svg?branch=master
[coveralls_legacy_link]: https://coveralls.io/github/symfony-cmf/routing?branch=master
[coveralls_stable_badge]: https://coveralls.io/repos/github/symfony-cmf/routing/badge.svg?branch=master
[coveralls_stable_link]: https://coveralls.io/github/symfony-cmf/routing?branch=master
[coveralls_unstable_badge]: https://coveralls.io/repos/github/symfony-cmf/routing/badge.svg?branch=master
[coveralls_unstable_link]: https://coveralls.io/github/symfony-cmf/routing?branch=master
