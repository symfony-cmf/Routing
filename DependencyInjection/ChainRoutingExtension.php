<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class ChainRoutingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = array();
        foreach ($configs as $subConfig) {
            $config = array_merge($config, $subConfig);
        }

        $controllers_by_alias = array();
        if (isset($config['chain_routing']['controllers_by_alias']))
        {
            $controllers_by_alias = $config['chain_routing']['controllers_by_alias'];
        }

        $container->setParameter('symfony_cmf_controllers_by_alias', $controllers_by_alias);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/';
    }

    public function getNamespace()
    {
        return 'http://www.example.com/symfony/schema/';
    }
}
