<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event fired before the dynamic router generates a url for a route.
 *
 * The name, parameters and absolute properties have the semantics of
 * UrlGeneratorInterface::generate()
 *
 * @author Ben Glassman
 *
 * @see \Symfony\Component\Routing\Generator\UrlGeneratorInterface::generate()
 */
final class RouterGenerateEvent extends Event
{
    /**
     * The name of the route to generate.
     */
    private string $route;

    /**
     * The parameters to use when generating the url.
     *
     * @var array<string, mixed>
     */
    private array $parameters;

    /**
     * The type of reference to be generated (one of the constants in UrlGeneratorInterface).
     */
    private int $referenceType;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(string $route, array $parameters, int $referenceType)
    {
        $this->route = $route;
        $this->parameters = $parameters;
        $this->referenceType = $referenceType;
    }

    /**
     * Get route name or object.
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRoute(string $route): void
    {
        $this->route = $route;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function setParameter(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Remove a route parameter by key.
     */
    public function removeParameter(string $key): void
    {
        unset($this->parameters[$key]);
    }

    /**
     * The type of reference to be generated (one of the constants in UrlGeneratorInterface).
     */
    public function getReferenceType(): int
    {
        return $this->referenceType;
    }

    /**
     * The type of reference to be generated (one of the constants in UrlGeneratorInterface).
     */
    public function setReferenceType(int $referenceType): void
    {
        $this->referenceType = $referenceType;
    }
}
