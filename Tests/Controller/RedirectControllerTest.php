<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Controller;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Controller\RedirectController;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Test\CmfUnitTestCase;

class RedirectControllerTest extends CmfUnitTestCase
{
    public function testConstructor()
    {
        new RedirectController($this->buildMock('Symfony\Component\Routing\RouterInterface'));
    }

    // the rest is covered by functional test
}