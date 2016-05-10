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
 * This enhancer can fill one field with the result of a hashmap lookup of
 * another field. If the target field is already set, it does nothing.
 *
 * @author David Buchmann
 */
class FieldMapEnhancer implements RouteEnhancerInterface, WithMapping
{
    /**
     * @var string field for key in hashmap lookup
     */
    protected $source;
    /**
     * @var string field to write hashmap lookup result into
     */
    protected $target;
    /**
     * @var array containing the mapping between the source field value and target field value
     */
    protected $mapping;
    
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $source the field to read
     * @param string $target the field to write the result of the lookup into
     * @param string $name
     */
    public function __construct($source, $target, $name)
    {
        $this->source = $source;
        $this->target = $target;
        $this->name = $name;
    }

    /**
     * If the target field is not set but the source field is, map the field.
     *
     * {@inheritdoc}
     */
    public function enhance(array $defaults, Request $request)
    {
        if (isset($defaults[$this->target])) {
            return $defaults;
        }
        if (!isset($defaults[$this->source])) {
            return $defaults;
        }
        if (!isset($this->mapping[$defaults[$this->source]])) {
            return $defaults;
        }

        $defaults[$this->target] = $this->mapping[$defaults[$this->source]];

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
     * An enhancer that needs special parameters to work with.
     *
     * @param $mapping
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }
}
