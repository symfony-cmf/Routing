<?php

namespace Symfony\Cmf\Component\Routing\Resolver;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Decide the controller by a map from alias to controller name injected into
 * the resolver.
 * Only works with route objects that return a 'type' field in the defaults.
 *
 * @author David Buchmann
 */
class ControllerAliasResolver implements ControllerResolverInterface
{
    /**
     * array containing the mapping between phpcr_alias and controller
     * i.e array('static_pages' => 'symfony_cmf_content.controller:indexAction')
     */
    private $controllersByAlias;

    public function __construct(array $controllersByAlias = array())
    {
        $this->controllersByAlias = $controllersByAlias;
    }

    /**
     * If the defaults specify a 'type' and if the injected map contains a
     * controller, returns that controller.
     *
     * {@inheritDoc}
     */
    public function getController(RouteObjectInterface $document, array &$defaults)
    {
        if (! isset($defaults['type'])) {
            return false;
        }
        if (! isset($this->controllersByAlias[$defaults['type']])) {
            return false;
        }

        return $this->controllersByAlias[$defaults['type']];
    }

}
