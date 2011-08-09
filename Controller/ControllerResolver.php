<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Controller;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;

class ControllerResolver
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
     * Retrieves the right controller for the given $document.
     */
    public function getController(RouteObjectInterface $document)
    {
        $defaults = $document->getRouteDefaults();
        if (isset($this->controllers_by_alias[$defaults['type']])) {
            $defaults['_controller'] = $this->controllers_by_alias[$defaults['type']];
            return $defaults;
        }
    }

}
