# Symfony CMF Routing

[![Latest Stable Version](https://poser.pugx.org/symfony-cmf/routing/v/stable)](https://packagist.org/packages/symfony-cmf/routing)
[![Latest Unstable Version](https://poser.pugx.org/symfony-cmf/routing/v/unstable)](https://packagist.org/packages/symfony-cmf/routing)
[![License](https://poser.pugx.org/symfony-cmf/routing/license)](https://packagist.org/packages/symfony-cmf/routing)

[![Total Downloads](https://poser.pugx.org/symfony-cmf/routing/downloads)](https://packagist.org/packages/symfony-cmf/routing)
[![Monthly Downloads](https://poser.pugx.org/symfony-cmf/routing/d/monthly)](https://packagist.org/packages/symfony-cmf/routing)
[![Daily Downloads](https://poser.pugx.org/symfony-cmf/routing/d/daily)](https://packagist.org/packages/symfony-cmf/routing)

Branch | Travis | Coveralls | Scrutinizer |
------ | ------ | --------- | ----------- |
2.1   | [![Build Status][travis_stable_badge]][travis_stable_link]     | [![Coverage Status][coveralls_stable_badge]][coveralls_stable_link]     | [![Scrutinizer Status][scrutinizer_stable_badge]][scrutinizer_stable_link] |
dev-master | [![Build Status][travis_unstable_badge]][travis_unstable_link] | [![Coverage Status][coveralls_unstable_badge]][coveralls_unstable_link] | [![Scrutinizer Status][scrutinizer_unstable_badge]][scrutinizer_unstable_link] |


This package is part of the [Symfony Content Management Framework (CMF)](https://cmf.symfony.com/) and licensed
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

* PHP 7.1 / 7.2 / 7.3
* Symfony 3.4 / 4.1 / 4.2
* See also the `require` section of [composer.json](composer.json)

## Documentation

For the install guide and reference, see:

* [symfony-cmf/routing Documentation](https://symfony.com/doc/master/cmf/components/routing/index.html)

See also:

* [All Symfony CMF documentation](https://symfony.com/doc/master/cmf/index.html) - complete Symfony CMF reference
* [Symfony CMF Website](https://cmf.symfony.com/) - introduction, live demo, support and community links

## Support

For general support and questions, please use [StackOverflow](https://stackoverflow.com/questions/tagged/symfony-cmf).

## Contributing

Pull requests are welcome. Please see our
[CONTRIBUTING](https://github.com/symfony-cmf/blob/master/CONTRIBUTING.md)
guide.

Unit and/or functional tests exist for this package. See the
[Testing documentation](https://symfony.com/doc/master/cmf/components/testing.html)
for a guide to running the tests.

Thanks to
[everyone who has contributed](contributors) already.

## License

This package is available under the [MIT license](src/Resources/meta/LICENSE).

[travis_stable_badge]: https://travis-ci.org/symfony-cmf/Routing.svg?branch=2.1
[travis_stable_link]: https://travis-ci.org/symfony-cmf/Routing
[travis_unstable_badge]: https://travis-ci.org/symfony-cmf/Routing.svg?branch=dev-master
[travis_unstable_link]: https://travis-ci.org/symfony-cmf/Routing

[coveralls_stable_badge]: https://coveralls.io/repos/github/symfony-cmf/Routing/badge.svg?branch=2.1
[coveralls_stable_link]: https://coveralls.io/github/symfony-cmf/Routing?branch=2.1
[coveralls_unstable_badge]: https://coveralls.io/repos/github/symfony-cmf/Routing/badge.svg?branch=dev-master
[coveralls_unstable_link]: https://coveralls.io/github/symfony-cmf/Routing?branch=dev-master

[scrutinizer_stable_badge]: https://scrutinizer-ci.com/g/symfony-cmf/Routing/badges/quality-score.png?b=2.1
[scrutinizer_stable_link]: https://scrutinizer-ci.com/g/symfony-cmf/Routing/?branch=2.1
[scrutinizer_unstable_badge]: https://scrutinizer-ci.com/g/symfony-cmf/Routing/badges/quality-score.png?b=dev-master
[scrutinizer_unstable_link]: https://scrutinizer-ci.com/g/symfony-cmf/Routing/?branch=dev-master
