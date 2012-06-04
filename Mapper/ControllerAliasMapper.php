<?php

namespace Symfony\Cmf\Component\Routing\Mapper;

use Symfony\Component\Routing\Route;

/**
 * Decide the controller by a map from alias to controller name injected into
 * the mapper.
 * Only works with route objects that return a 'type' field in the defaults.
 *
 * @author David Buchmann
 */
class ControllerAliasMapper implements ControllerMapperInterface
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
    public function getController(Route $route, array &$defaults)
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
