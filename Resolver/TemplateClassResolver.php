<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Resolver;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteObjectInterface;

/**
 * Use the generic content controller with a template indicated by the map of
 * content class names to template names.
 *
 * Only works with route objects that return a referenced content.
 *
 * @author David Buchmann
 */
class TemplateClassResolver implements ControllerResolverInterface
{
    /**
     * the controller name or service name that will accept a content and a
     * template
     *
     * @var string
     */
    private $generic_controller;

    /**
     * Map of full class names to template names
     *
     * @var array of strings
     */
    private $templates_by_class;

    /**
     * Instantiate the template resolver
     *
     * @param string $generic_controller the controller name or service name
     *      that will accept a content and a template
     * @param array $templates_by_class a map between class and template
     *      i.e array('Symfony/Cmf/Bundle/ContentBundle/Document/StaticContent' =>
     *                'SandboxMainBundle:EditableStaticContent:nosidebar.html.twig')
     */
    public function __construct($generic_controller, array $templates_by_class = array())
    {
        $this->generic_controller = $generic_controller;
        $this->templates_by_class = $templates_by_class;
    }

    /**
     * Checks if the $document has a content and if so tries to find a match in
     * the templates_by_class map. If a template is found, it is added to the
     * $defaults and the generic_controller is returned.
     *
     * {@inheritDoc}
     */
    public function getController(RouteObjectInterface $document, array &$defaults)
    {
        $content = $document->getRouteContent();
        if (null == $content) {
            return false;
        }

        $template = false;
        // we need to loop over the array in case the content class extends the
        // specified class
        // i.e. phpcr-odm generates proxy class for the content.
        foreach($this->templates_by_class as $class => $t) {
            if ($content instanceof $class) {
                $template = $t;
                break;
            }
        }

        if (! $template) {
            return false;
        }

        $defaults['template'] = $template;
        return $this->generic_controller;
    }

}
