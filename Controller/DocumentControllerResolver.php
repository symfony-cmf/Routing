<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;

class DocumentControllerResolver
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
    public function getController($document)
    {
        $controller = $document->getController();

        if (array_key_exists($controller, $this->controllers_by_alias)) {
            return $this->controllers_by_alias[$controller];
        }
    }

}
