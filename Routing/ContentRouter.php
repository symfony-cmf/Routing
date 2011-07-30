<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Routing;

use Symfony\Component\Routing\Router;

class ContentRouter extends Router
{
    protected $document_manager;
    protected $controller_resolver;

    public function setDocumentManager(\Doctrine\ODM\PHPCR\DocumentManager $dm)
    {
        $this->document_manager = $dm;
    }

    public function setControllerResolver(\Symfony\Cmf\Bundle\ChainRoutingBundle\Controller\DocumentControllerResolver $cr)
    {
        $this->controller_resolver = $cr;
    }

    /**
     * Returns an array of parameter like this
     *
     * array(
     *   "_controller" => "NameSpace\Controller::action", 
     *   "document" => $document)
     *
     * @param string $url
     * @return array
     */
    public function match($url)
    {
        $node = $this->document_manager->find(null, $url);

        if (!$node || !\method_exists($node, 'getReference'))
        {
            return false;
        }

        $document = $node->getReference();
        $controller = $this->controller_resolver->getController($document);

        if (!$controller)
        {
            return false;
        }

        return array('_controller'  => $controller,
                     'document'     => $document);

    }
    
}
