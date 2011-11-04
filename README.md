# Symfony Chain Routing Bundle

## ChainRouter

This bundle contains a extension to the normal Symfony Router. The different is this contains a series
of ordinary Routers which uses priorities to determaine the first router that gets to match a given
url.

To register a new router just add it as a service tagged with `router` and an optional `priority`.
The higher the priority, the earlier your router will be asked to match the route. If you give no priority,
your router will come last.
If there are several routers with the same priority, the order is undetermined.

For example, the cmf router is loaded with

    <service id="symfony_cmf_router.content_router" class="%symfony_cmf_router.content_router_class%">
        <tag name="router" priority="200" />

("Symfony documentation for DependencyInjection tags.")[http://symfony.com/doc/2.0/reference/dic_tags.html]

## TODO

* More configuration for the chain router
** Should the chain router replace the default symfony router?
** Put the default_router of symfony into the chain? At what priority?
** Allow a map of router service names to priorities as alternative to the tags
* CMF content router
** Implement generate and getRouteCollection
** Name is badly chosen, it is a database router, not a content router
** Configuration for the entity/document class name so it can work with ORM too
** More options for the Controller Resolver?
* More documentation

## Authors

* Filippo De Santis (p16)
* Henrik Bjornskov (henrikbjorn)
* Claudio Beatrice (omissis)
* Lukas Kahwe Smith (lsmith77)
* David Buchmann (dbu)

The original code for the chain router was contributed by Magnus Nordlander.