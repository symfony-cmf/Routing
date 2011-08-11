# Symfony Chain Routing Bundle

## ChainRouter

This bundle contains a extension to the normal Symfony Router. The different is this contains a series
of ordinary Routers which uses priorities to determaine the first router that gets to match a given
url.

To register a new router just add it as a service tagged with `router` and provide a voluntary `priority`.

("Symfony documentation for DependencyInjection tags.")[http://symfony.com/doc/2.0/reference/dic_tags.html]
