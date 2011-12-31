<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;

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
    private $controllers_by_alias;

    public function __construct(array $controllers_by_alias = array())
    {
        $this->controllers_by_alias = $controllers_by_alias;
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
        if (! isset($this->controllers_by_alias[$defaults['type']])) {
            return false;
        }

        return $this->controllers_by_alias[$defaults['type']];
    }

}
