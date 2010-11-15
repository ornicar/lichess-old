<?php

require_once __DIR__.'/../src/autoload.php';

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LichessKernel extends Kernel
{
    public function registerRootDir()
    {
        return __DIR__;
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\ZendBundle\ZendBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Bundle\ApcBundle\ApcBundle(),
            new Bundle\TimeBundle\TimeBundle(),
            new Symfony\Bundle\DoctrineMongoDBBundle\DoctrineMongoDBBundle(),
            new Bundle\LichessBundle\LichessBundle(),
            new Bundle\DoctrineUserBundle\DoctrineUserBundle(),
            new Bundle\ForumBundle\ForumBundle(),
            new Application\DoctrineUserBundle\DoctrineUserBundle(),
            new Application\ForumBundle\ForumBundle()
        );

        if ($this->isDebug()) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function registerBundleDirs()
    {
        return array(
            'Application'     => __DIR__.'/../src/Application',
            'Bundle'          => __DIR__.'/../src/Bundle',
            'Symfony\\Bundle' => __DIR__.'/../src/vendor/Symfony/src/Symfony/Bundle',
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');

        if (!$this->isDebug()) {
            $container = new ContainerBuilder();
            $container->setParameter('exception_listener.controller', 'LichessBundle:Main:notFound');
            return $container;
        }
    }
}
