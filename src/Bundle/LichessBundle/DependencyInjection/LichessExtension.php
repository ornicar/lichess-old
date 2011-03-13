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
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('chess.xml');
        $loader->load('model.xml');
        $loader->load('blamer.xml');
        $loader->load('critic.xml');
        $loader->load('elo.xml');
        $loader->load('controller.xml');
        $loader->load('twig.xml');
        $loader->load('translation.xml');
        $loader->load('form.xml');
        $loader->load('logger.xml');
        $loader->load('cheat.xml');
        $loader->load('starter.xml');
        $loader->load('seek.xml');
        $loader->load('akismet.xml');
        $loader->load('provider.xml');
        $loader->load('listener.xml');
        $loader->load('game_config.xml');

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->process($configuration->getConfigTree(), $configs);

        $container->setParameter('lichess.ai.class', $config['ai']['class']);
        $container->setParameter('lichess.ai.crafty_path', $config['ai']['crafty_path']);
        $container->setParameter('lichess.translation.remote_domain', $config['translation']['remote_domain']);
        $container->setParameter('lichess.debug_assets', $config['debug_assets']);
        $container->setParameter('akismet.api_key', $config['akismet']['api_key']);
        $container->setParameter('akismet.url', $config['akismet']['url']);
        $container->setParameter('lichess.seek_matcher.use_session', $config['seek']['use_session']);
        $container->setParameter('lichess.starter.anybody.check_creator_is_connected', $config['anybody_starter']['check_creator_is_connected']);

        if ($config['test']) {
            $loader->load('test.xml');
        }
        $container->setParameter("lichess.form.add_color_hidden_field", $config['test']);
    }
}
