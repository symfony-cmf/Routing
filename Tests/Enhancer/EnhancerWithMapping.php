<?php

namespace Symfony\Cmf\Component\Routing\Tests\Enhancer;

use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Cmf\Component\Routing\Enhancer\WithMapping;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
interface EnhancerWithMapping extends RouteEnhancerInterface, WithMapping
{

}
