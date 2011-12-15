<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;

/**
 * Decide the controller by a map from class name to controller name injected
 * into the resolver.
 * Only works with route objects that return a referenced content.
 *
 * @author David Buchmann
 */
class ControllerClassResolver implements ControllerResolverInterface
{
    private $controllers_by_class;

    /**
     * @param array $controllers_by_class a map between class and controller
     *      i.e array('Symfony/Cmf/Bundle/ContentBundle/Document/StaticContent' =>
     *                'symfony_cmf_content.controller:indexAction')
     */
    public function __construct(array $controllers_by_class = array())
    {
        $this->controllers_by_class = $controllers_by_class;
    }

    /**
     * Checks if the defaults specify a 'type' and if the injected map contains a controller
     */
    public function getController(RouteObjectInterface $document)
    {
        $content = $document->getRouteContent();
        if (null == $content) {
            return false;
        }
        $class = get_class($content);

        if (! isset($this->controllers_by_class[$class])) {
            return false;
        }

        return $this->controllers_by_class[$class];
    }

}
