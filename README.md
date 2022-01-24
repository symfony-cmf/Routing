# Symfony CMF Routing

[![Build Status](https://github.com/symfony-cmf/Routing/actions/workflows/test-application.yaml/badge.svg)](https://github.com/symfony-cmf/Routing/actions/workflows/test-application.yaml)
[![Latest Stable Version](https://poser.pugx.org/symfony-cmf/routing/v/stable)](https://packagist.org/packages/symfony-cmf/routing)
[![Latest Unstable Version](https://poser.pugx.org/symfony-cmf/routing/v/unstable)](https://packagist.org/packages/symfony-cmf/routing)
[![License](https://poser.pugx.org/symfony-cmf/routing/license)](https://packagist.org/packages/symfony-cmf/routing)

[![Total Downloads](https://poser.pugx.org/symfony-cmf/routing/downloads)](https://packagist.org/packages/symfony-cmf/routing)
[![Monthly Downloads](https://poser.pugx.org/symfony-cmf/routing/d/monthly)](https://packagist.org/packages/symfony-cmf/routing)
[![Daily Downloads](https://poser.pugx.org/symfony-cmf/routing/d/daily)](https://packagist.org/packages/symfony-cmf/routing)

This package is part of the Symfony Content Management Framework (CMF) and licensed
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

See the `require` section of [composer.json](composer.json)

## Documentation

For the install guide and reference, see:

* [symfony-cmf/routing Documentation](https://symfony.com/bundles/CMFRoutingBundle/current/routing-component/index.html)

See also:

* [All Symfony CMF documentation](https://symfony.com/bundles/CMFRoutingBundle/current/index.html) - complete Symfony CMF reference

## Support

For general support and questions, please use [StackOverflow](http://stackoverflow.com/questions/tagged/symfony-cmf).

## Contributing

Pull requests are welcome. Please see our
[CONTRIBUTING](https://github.com/symfony-cmf/Routing/blob/master/CONTRIBUTING.md)
guide.

Thanks to [everyone who has contributed](contributors) already.

## License

This package is available under the [MIT license](src/Resources/meta/LICENSE).
