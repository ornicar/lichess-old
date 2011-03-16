<?php

namespace Application\ForumBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\Config\FileLocator;

class LichessForumExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('form.xml');
        $loader->load('akismet.xml');
        $loader->load('authorname_persistence.xml');
        $loader->load('timeline.xml');

        $config = array();
        foreach ($configs as $c) {
            $config = array_merge($config, $c);
        }

        if (!empty($config['detect_spam'])) {
            $container->setParameter('forum.akismet.enabled', true);
        }
    }
}
