<?php

namespace Symfony\Cmf\Component\Routing\Mapper;

use Symfony\Component\Routing\Route;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * If the route object provides a '_template' field in the defaults, return
 * the configured generic controller to handle this content
 *
 * @author David Buchmann
 */
class ExplicitTemplateMapper implements ControllerMapperInterface
{
    /**
     * the controller name or service name that will accept a content and a
     * template
     *
     * @var string
     */
    private $genericController;

    /**
     * Instantiate the template mapper
     *
     * @param string $genericController the controller name or service name
     *      that will accept a content and a template
     */
    public function __construct($genericController)
    {
        $this->genericController = $genericController;
    }

    /**
     * Checks if the defaults specify a '_template' and if so returns the
     * generic controller
     *
     * {@inheritDoc}
     */
    public function getController(Route $document, array &$defaults)
    {
        if (! isset($defaults[RouteObjectInterface::TEMPLATE_NAME])) {
            return false;
        }

        return $this->genericController;
    }

}
