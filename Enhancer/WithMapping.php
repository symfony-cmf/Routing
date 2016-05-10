<?php

namespace Symfony\Cmf\Component\Routing\Enhancer;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
interface WithMapping
{
    /**
     * An enhancer that needs special parameters to work with.
     *
     * @param $mapping
     */
    public function setMapping($mapping);

    /**
     * Decider whether a enhancer got that name or not.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isName($name);
}
