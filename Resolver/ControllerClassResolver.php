<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;

/**
 * Decide the controller by a map from class to controller name injected into
 * the resolver. The comparison is done with instanceof to support proxy
 * classes and such.
 *
 * Only works with route objects that return a referenced content.
 *
 * @author David Buchmann
 */
class ControllerClassResolver implements ControllerResolverInterface
{
    private $controllersByClass;

    /**
     * @param array $controllersByClass a map between class and controller
     *      i.e array('Symfony/Cmf/Bundle/ContentBundle/Document/StaticContent' =>
     *                'symfony_cmf_content.controller:indexAction')
     */
    public function __construct(array $controllersByClass)
    {
        $this->controllersByClass = $controllersByClass;
    }

    /**
     * If the route has a non-null content and if that content class is in the
     * injected map, returns that controller.
     *
     * {@inheritDoc}
     *
     * @param array $defaults ignored
     */
    public function getController(RouteObjectInterface $document, array &$defaults)
    {
        $content = $document->getRouteContent();
        if (null == $content) {
            return false;
        }
        // we need to loop over the array in case the content class extends the
        // specified class
        // i.e. phpcr-odm generates proxy class for the content.
        foreach($this->controllersByClass as $class => $controller) {
            if ($content instanceof $class) {
                return $controller;
            }
        }

        return false;
    }

}
