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
 * This enhancer can fill one field with the result of a hashmap lookup of
 * another field. If the target field is already set, it does nothing.
 *
 * @author David Buchmann
 */
final class FieldMapEnhancer implements RouteEnhancerInterface
{
    private string $keyFieldName;
    private string $targetFieldName;

    /**
     * @var array<string, mixed> containing the mapping between the source field value and target field value
     */
    private array $hashmap;

    /**
     * @param array<string, mixed> $hashmap for looking up value from keyFieldName and get value to put into targetFieldName
     */
    public function __construct(string $keyFieldName, string $targetFieldName, array $hashmap)
    {
        $this->keyFieldName = $keyFieldName;
        $this->targetFieldName = $targetFieldName;
        $this->hashmap = $hashmap;
    }

    /**
     * If the target field is not set but the source field is, map the field.
     *
     * {@inheritdoc}
     */
    public function enhance(array $defaults, Request $request): array
    {
        if (array_key_exists($this->targetFieldName, $defaults)
            || !array_key_exists($this->keyFieldName, $defaults)
            || !array_key_exists($defaults[$this->keyFieldName], $this->hashmap)
        ) {
            return $defaults;
        }

        $defaults[$this->targetFieldName] = $this->hashmap[$defaults[$this->keyFieldName]];

        return $defaults;
    }
}
