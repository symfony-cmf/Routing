<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTestCase extends WebTestCase
{
    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager
     */
    protected static $dm;

    static protected function createKernel(array $options = array())
    {
        return new AppKernel(
            isset($options['config']) ? $options['config'] : 'default.yml'
        );
    }

    /**
     * @param array $options passed to self:.createKernel
     * @param string $routebase base name for routes under /test to use
     */
    public static function setupBeforeClass(array $options = array(), $routebase = null)
    {
        self::$kernel = self::createKernel($options);
        self::$kernel->init();
        self::$kernel->boot();

        self::$dm = self::$kernel->getContainer()->get('doctrine_phpcr.odm.document_manager');

        if (null == $routebase) {
            return;
        }

        $session = self::$kernel->getContainer()->get('doctrine_phpcr.session');
        if ($session->nodeExists("/test/$routebase")) {
            $session->getNode("/test/$routebase")->remove();
        }
        if (! $session->nodeExists('/test')) {
            $session->getRootNode()->addNode('test', 'nt:unstructured');
        }
        $baseroute = $session->getNode('/test')->addNode($routebase, 'nt:unstructured');
        $baseroute->addMixin('mix:referenceable');
        $session->save();
    }
}