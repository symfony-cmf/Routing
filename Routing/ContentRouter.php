<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

use Symfony\Component\Routing\Router;
use Doctrine\Common\Persistence\ObjectManager;

class ContentRouter extends Router
{

    protected $om;
    protected $controller_resolver;

    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function setControllerResolver(\Symfony\Cmf\Bundle\ChainRoutingBundle\Controller\ControllerResolver $cr)
    {
        $this->controller_resolver = $cr;
    }

    /**
     * Returns an array of parameter like this
     *
     * array(
     *   "_controller" => "NameSpace\Controller::action", 
     *   "reference" => $document,
     * )
     *
     * @param string $url
     * @return array
     */
    public function match($url)
    {
        $document = $this->om->find(null, $url);

        if (!$document  instanceof RouteObjectInterface) {
            return false;
        }

        $defaults = $this->controller_resolver->getController($document);
        if (empty($defaults['_controller'])) {
            return false;
        }

        $defaults['reference'] = $document->getReference();

        return $defaults;
    }

}
