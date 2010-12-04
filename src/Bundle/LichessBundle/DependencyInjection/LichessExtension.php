<?php

namespace Bundle\LichessBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LichessExtension extends Extension
{
    public function configLoad($config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
        $loader->load('chess.xml');
        $loader->load('model.xml');
        $loader->load('blamer.xml');
        $loader->load('critic.xml');
        $loader->load('elo.xml');
        $loader->load('controller.xml');
        $loader->load('templating.xml');
        $loader->load('translation.xml');
        $loader->load('form.xml');
        $loader->load('security.xml');

        if (!isset($config['db_driver'])) {
            throw new \InvalidArgumentException('You must provide the lichess.db_driver configuration');
        }

        if ($config['db_driver'] == 'odm') {
            $container->setAlias('lichess.object_manager', 'doctrine.odm.mongodb.document_manager');
        } else {
            $container->setAlias('lichess.object_manager', 'doctrine.orm.entity_manager');
        }

        if(isset($config['ai']['class'])) {
            $container->setParameter('lichess.ai.class', $config['ai']['class']);
        }

        if(isset($config['storage']['class'])) {
            $container->setParameter('lichess.storage.class', $config['storage']['class']);
        }

        if(isset($config['translation']['remote_domain'])) {
            $container->setParameter('lichess.translation.remote_domain', $config['translation']['remote_domain']);
        }

        if('test' === $container->getParameter('kernel.environment')) {
            $container->setAlias('session.storage', 'session.storage.test');
        }
    }

    public function testLoad($config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
        $loader->load('test.xml');
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return null;
    }

    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/dic/symfony';
    }

    public function getAlias()
    {
        return 'lichess';
    }

}
