<?php

namespace Bundle\LichessBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class LichessExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->process($configuration->getConfigTree(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('model.xml');
        $loader->load('provider.xml');
        $loader->load('sync.xml');
        $loader->load('lila.xml');
        $loader->load('messenger.xml');
        foreach ($config['feature'] as $feature => $enabled) {
            if ($enabled) $loader->load($feature.'.xml');
        }

        if ($config['feature']['ai']) {
            foreach (array('enabled', 'priority', 'executable_path', 'book_dir') as $option) {
                $container->setParameter('lichess.ai.crafty.'.$option, $config['ai']['crafty'][$option]);
            }
            foreach (array('enabled', 'priority') as $option) {
                $container->setParameter('lichess.ai.stupid.'.$option, $config['ai']['stupid'][$option]);
            }
        }
        $container->setParameter('lichess.lila.internal_url', $config['lila']['internal_url']);
        $container->setParameter('lichess.debug_assets', $config['debug_assets']);
        $container->setParameter('lichess.sync.path', $config['sync']['path']);
        $container->setParameter('lichess.sync.latency', $config['sync']['latency']);
        $container->setParameter('lichess.sync.delay', $config['sync']['delay']);

        if ($config['test']) {
            $loader->load('test.xml');
        }
    }
}
