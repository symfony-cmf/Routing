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
class FieldByClassEnhancer implements RouteEnhancerInterface, WithMapping
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
    protected $mapping;

    /**
     * @var
     */
    private $name;

    /**
     * @param string $source the field name of the class
     * @param string $target the field name to set from the map
     * @param string $name
     */
    public function __construct($source, $target, $name)
    {
        $this->source = $source;
        $this->target = $target;
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
        foreach ($this->mapping as $class => $value) {
            if ($defaults[$this->source] instanceof $class) {
                // found a matching entry in the map
                $defaults[$this->target] = $value;

                return $defaults;
            }
        }

        return $defaults;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isName($name)
    {
        return $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }
}
