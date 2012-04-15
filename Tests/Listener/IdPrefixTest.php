<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Listener;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Listener\IdPrefix;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;

class IdPrefixTest extends CmfUnitTestCase
{
    public function testConstructor()
    {
        new IdPrefix('test');
    }

    // the rest is covered by functional test
}