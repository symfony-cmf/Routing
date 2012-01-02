<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;

/**
 * If the route object provides a 'template' field in the defaults, return
 * the configured generic controller to handle this content
 *
 * @author David Buchmann
 */
class ExplicitTemplateResolver implements ControllerResolverInterface
{
    /**
     * the controller name or service name that will accept a content and a
     * template
     *
     * @var string
     */
    private $genericController;

    /**
     * Instantiate the template resolver
     *
     * @param string $genericController the controller name or service name
     *      that will accept a content and a template
     */
    public function __construct($genericController)
    {
        $this->genericController = $genericController;
    }

    /**
     * Checks if the defaults specify a 'template' and if so returns the
     * generic controller
     *
     * {@inheritDoc}
     */
    public function getController(RouteObjectInterface $document, array &$defaults)
    {
        if (! isset($defaults['template'])) {
            return false;
        }

        return $this->genericController;
    }

}
