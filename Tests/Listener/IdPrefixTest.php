<?php

namespace Symfony\Cmf\Component\Routing\Tests\Listener;

use Symfony\Cmf\Component\Routing\Listener\IdPrefix;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class IdPrefixTest extends CmfUnitTestCase
{
    public function testConstructor()
    {
        new IdPrefix('test');
    }

    // the rest is covered by functional test
}