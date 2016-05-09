<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Enhancer;

use Symfony\Component\HttpFoundation\Request;

/**
 * This enhancer sets a field if not yet existing from the class of an object
 * in another field.
 *
 * The comparison is done with instanceof to support proxy classes and such.
 *
 * Only works with RouteObjectInterface routes that can return a referenced
 * content.
 *
 * @author David Buchmann
 */
class FieldByClassEnhancer implements RouteEnhancerInterface
{
    /**
     * @var string field for the source class
     */
    protected $source;
    /**
     * @var string field to write hashmap lookup result into
     */
    protected $target;
    /**
     * @var array containing the mapping between a class name and the target value
     */
    protected $map;

    /**
     * Key that can should be used in a http method aware defaults configuration.
     */
    const KEY_METHODS = 'methods';
    const KEY_CONTROLLER = 'controller';

    /**
     * Matches all http methods.
     */
    const METHOD_ANY = 'any';

    /**
     * @var
     */
    private $name;

    /**
     * @param string $source the field name of the class
     * @param string $target the field name to set from the map
     * @param array $map the map of class names to field values
     * @param string $name
     */
    public function __construct($source, $target, $map, $name)
    {
        $this->source = $source;
        $this->target = $target;
        $this->map = $map;
        $this->name = $name;
    }

    /**
     * If the source field is instance of one of the entries in the map,
     * target is set to the value of that map entry.
     *
     * {@inheritdoc}
     */
    public function enhance(array $defaults, Request $request)
    {
        if (isset($defaults[$this->target])) {
            // no need to do anything
            return $defaults;
        }

        if (!isset($defaults[$this->source])) {
            return $defaults;
        }

        // we need to loop over the array and do instanceof in case the content
        // class extends the specified class
        // i.e. phpcr-odm generates proxy class for the content.
        foreach ($this->map as $class => $value) {
            if ($defaults[$this->source] instanceof $class) {
                // found a matching entry in the map
                $defaults[$this->target] = is_array($value) ? $this->handleHTTPMethodAware($value, $request) : $value;

                return $defaults;
            }
        }

        return $defaults;
    }

    /**
     * Some values of an map entry can contain http method depending configuration to set i.e. the controller.
     *
     * @param []      $values
     * @param Request $request
     *
     * @return string|array
     */
    private function handleHTTPMethodAware($values, Request $request)
    {
        foreach ($values as $value) {
            if (!isset($value[self::KEY_METHODS]) || !isset($value[self::KEY_CONTROLLER])) {
                continue;
            }

            if (is_array($value[self::KEY_METHODS])
                && (in_array(strtolower($request->getMethod()), $value[self::KEY_METHODS])
                || in_array(self::METHOD_ANY, $value[self::KEY_METHODS]))
            ) {
                return $value[self::KEY_CONTROLLER];
            }
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function isName($name)
    {
        return $this->name = $name;
    }
}
