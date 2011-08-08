<?php

namespace Symfony\Bundle\ChainRoutingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ChainRoutingExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

foreach ($container->getParameterBag()->all() as $k => $p) {
    echo $k, " => "; var_dump($p);
}
var_dump($config);
exit;

        // implement a service that get the subrouters from the app config file
        // and then populate the chainrouter accordingy
        //
        // TODO: move the alias to the compiler pass because there I'm going to be sure to have all the shit loaded.
        // TODO: write some tests
    }
}
