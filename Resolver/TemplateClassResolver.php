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
    private $genericController;

    /**
     * Map of full class names to template names
     *
     * @var array of strings
     */
    private $templatesByClass;

    /**
     * Instantiate the template resolver
     *
     * @param string $genericController the controller name or service name
     *      that will accept a content and a template
     * @param array $templatesByClass a map between class and template
     *      i.e array('Symfony/Cmf/Bundle/ContentBundle/Document/StaticContent' =>
     *                'SandboxMainBundle:EditableStaticContent:nosidebar.html.twig')
     */
    public function __construct($genericController, array $templatesByClass = array())
    {
        $this->genericController = $genericController;
        $this->templatesByClass = $templatesByClass;
    }

    /**
     * Checks if the $document has a content and if so tries to find a match in
     * the templatesByClass map. If a template is found, it is added to the
     * $defaults and the genericController is returned.
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
        foreach ($this->templatesByClass as $class => $t) {
            if ($content instanceof $class) {
                $template = $t;
                break;
            }
        }

        if (! $template) {
            return false;
        }

        $defaults['template'] = $template;
        return $this->genericController;
    }

}
