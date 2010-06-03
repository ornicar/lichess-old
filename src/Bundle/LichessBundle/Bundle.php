<?php

namespace Bundle\MiamBundle;

use Bundle\MiamBundle\DependencyInjection\MiamExtension;

use Symfony\Foundation\Bundle\Bundle as BaseBundle;

use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Components\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Components\DependencyInjection\BuilderConfiguration;

class Bundle extends BaseBundle
{
    public function buildContainer(ContainerInterface $container)
    {
        $configuration = new BuilderConfiguration();
        
        $loader = new XmlFileLoader(__DIR__.'/Resources/config');
        $configuration->merge($loader->load('auth.xml'));
        $configuration->merge($loader->load('observer.xml'));
        
        $loader = new YamlFileLoader(__DIR__.'/Resources/config');
        $configuration->merge($loader->load('colors.yml'));

        return $configuration;
    }

    /**
     * Boots the Bundle.
     *
     * @param Symfony\Components\DependencyInjection\ContainerInterface $container A ContainerInterface instance
     */
    public function boot(ContainerInterface $container)
    {
      $container->getMiamObserverService();
    }

}
