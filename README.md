# Symfony Chain Routing Bundle

This bundle contains a replacement for the default Symfony Router. As the name
implies, the chain router registers a list of routers that it tries by
priority to match and generate routes.

Additionally, this bundle delivers useful router implementations. Currently,
there is the DoctrineRouter that routes based on doctrine database entities
or documents.

## Installation

If you just use the chain router, this bundle has no dependencies.
For the DoctrineRouter you need a doctrine implementation. Default provided
classes use phpcr-odm which you can install as described in
[symfony-cmf](https://github.com/symfony-cmf/symfony-cmf)


## ChainRouter

The ChainRouter can replace the default symfony routing system with a chain-
enabled implementation. It does not route anything on its own, but only loops
through all chained routers. To handle standard configured symfony routes, the
symfony default router is used.

### Configuration

In your app/config/config.yml, you can specify which router services you want
to use. If you do not specify the routers_by_id map at all, by default the
chain router will just load the built-in symfony router. When you specify the
routers_by_id list, you need to explicitly specify router.default unless you
don't want the configuration based default router.

The format is ```service_name: priority``` - the higher the priority number the
earlier this router service is asked to match a route or to generate a url.

    symfony_cmf_chain_routing:
        chain:
            routers_by_id:
                # enable the DoctrineRouter with high priority to allow overwriting configured routes with content
                symfony_cmf_chain_routing.doctrine_router: 200
                # enable the symfony default router with a lower priority
                router.default: 100
            # whether the chain router should replace the default router. defaults to true
            # if you set this to false, you will need to do somthing else to trigger your router
            # replace_symfony_router: true

### Loading routers with tagging

Your routers can automatically register, just add it as a service tagged with `router` and an optional `priority`.
The higher the priority, the earlier your router will be asked to match the route. If you do not specify the priority,
your router will come last.
If there are several routers with the same priority, the order between them is undetermined.

For example, the cmf router is loaded with

    <service id="my_namespace.my_router" class="%my_namespace.my_router_class%">
        <tag name="router" priority="300" />

See also [Symfony documentation for DependencyInjection tags.](http://symfony.com/doc/2.0/reference/dic_tags.html)

## Doctrine Router

This implementation of a router generates url and matches requests with content
of a database. If you want to use this with the phpcr-odm, all you need to do
is specify configuration for the controller resolvers. If you want to change
something, have a look into Routing/DoctrineRouter.php

The router looks for the url in the database and determines the controller with
the help of this database object. It then specifies this controller for the
request and if the object returns a reference content object, this is passed as
argument to the controller.
The router uses ControllerResolver that can tell from the content which router
to call. The router will then be called with the content entity as argument.

If you do not use phpcr-odm, you might need to specify the class for the route
entity (in phpcr-odm, the class is automatically detected).

To configure the resolvers, you can specify mappings. Presence of each of the
mappings makes the DI container inject the respective resolver into the
DoctrineRouter.

The possible mappings are:
* Alias: requires the route document to return a 'type' value in getRouteDefaults()
* Class: requires the route document to have a reference content, the class
    name is the class of that content document.
* TODO: redirect controller
* TODO: explicit template and content
* TODO: template_by_class
* TODO: generic controller with output directed by annotations?

If the route returns a field '_controller' in getRouteDefaults, this router is used.

    symfony_cmf_chain_routing:
        doctrine:
            controllers_by_alias:
                editablestatic: sandbox_main.controller:indexAction
            controllers_by_class:
                Symfony\Cmf\Bundle\ContentBundle\Document\StaticContent: symfony_cmf_content.controller::indexAction

            # optional, to be used when routing with a doctrine object manager
            # that needs a class name for find. phpcr-odm can guess the name.
            # route_entity_class: Fully\Qualified\Classname

## TODO

* CMF content router
  * Implement generate and getRouteCollection

## Authors

* Filippo De Santis (p16)
* Henrik Bjornskov (henrikbjorn)
* Claudio Beatrice (omissis)
* Lukas Kahwe Smith (lsmith77)
* David Buchmann (dbu)

The original code for the chain router was contributed by Magnus Nordlander.