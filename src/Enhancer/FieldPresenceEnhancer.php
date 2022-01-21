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
 * This enhancer sets a field to a fixed value. You can specify a source field
 * name to only set the target field if the source is present.
 *
 * @author David Buchmann
 */
final class FieldPresenceEnhancer implements RouteEnhancerInterface
{
    /**
     * Field name for the source field that must exist. If null, the target
     * field is always set if not already present.
     */
    private ?string $fieldName;

    private string $targetFieldName;
    private mixed $value;

    public function __construct(?string $fieldName, string $targetFieldName, mixed $value)
    {
        $this->fieldName = $fieldName;
        $this->targetFieldName = $targetFieldName;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(array $defaults, Request $request): array
    {
        if (array_key_exists($this->targetFieldName, $defaults)) {
            return $defaults;
        }

        if (null !== $this->fieldName && !array_key_exists($this->fieldName, $defaults)) {
            return $defaults;
        }

        $defaults[$this->targetFieldName] = $this->value;

        return $defaults;
    }
}
