<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
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
final class FieldByClassEnhancer implements RouteEnhancerInterface
{
    private string $classFieldName;

    /**
     * @var string field to write hashmap lookup result into
     */
    private string $targetFieldName;

    /**
     * @var array<string, string> Hashmap from class name to target value
     */
    private array $map;

    /**
     * @param array<string, mixed> $map the map of class names to field values
     */
    public function __construct(string $classFieldName, string $targetFieldName, array $map)
    {
        $this->classFieldName = $classFieldName;
        $this->targetFieldName = $targetFieldName;
        $this->map = $map;
    }

    /**
     * If the source field is instance of one of the entries in the map,
     * target is set to the value of that map entry.
     *
     * {@inheritdoc}
     */
    public function enhance(array $defaults, Request $request): array
    {
        if (array_key_exists($this->targetFieldName, $defaults)
            || !array_key_exists($this->classFieldName, $defaults)
        ) {
            return $defaults;
        }

        // we need to loop over the array and do instanceof in case the content
        // class extends the specified class
        // i.e. phpcr-odm generates proxy class for the content.
        foreach ($this->map as $class => $value) {
            if ($defaults[$this->classFieldName] instanceof $class) {
                // found a matching entry in the map
                $defaults[$this->targetFieldName] = $value;

                return $defaults;
            }
        }

        return $defaults;
    }
}
