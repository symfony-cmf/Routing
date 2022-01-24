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

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * This enhancer sets the content to target field if the route provides content.
 *
 * Only works with RouteObjectInterface routes that can return a referenced
 * content.
 *
 * @author David Buchmann
 */
final class RouteContentEnhancer implements RouteEnhancerInterface
{
    /**
     * @var string field for the route class
     */
    private string $routeClassFieldName;
    private string $targetFieldName;

    public function __construct(string $routeClassFieldName, string $targetFieldName)
    {
        $this->routeClassFieldName = $routeClassFieldName;
        $this->targetFieldName = $targetFieldName;
    }

    /**
     * If the route has a non-null content and if that content class is in the
     * injected map, returns that controller.
     *
     * {@inheritdoc}
     */
    public function enhance(array $defaults, Request $request): array
    {
        if (array_key_exists($this->targetFieldName, $defaults)) {
            // no need to do anything
            return $defaults;
        }

        if (!array_key_exists($this->routeClassFieldName, $defaults)
            || !$defaults[$this->routeClassFieldName] instanceof RouteObjectInterface
        ) {
            // we can't determine the content
            return $defaults;
        }
        $route = $defaults[$this->routeClassFieldName];

        $content = $route->getContent();
        if (!$content) {
            // we have no content
            return $defaults;
        }
        $defaults[$this->targetFieldName] = $content;

        return $defaults;
    }
}
