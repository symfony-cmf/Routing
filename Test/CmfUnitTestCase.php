<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Test;

class CmfUnitTestCase extends \PHPUnit_Framework_TestCase
{
    protected function buildMock($class, array $methods = array())
    {
        return $this->getMockBuilder($class)
                    ->disableOriginalConstructor()
                    ->setMethods($methods)
                    ->getMock();
    }

}
