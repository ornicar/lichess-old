<?php

require_once __DIR__.'/../src/autoload.php';

use Symfony\Framework\Kernel;
use Symfony\Components\DependencyInjection\Loader\YamlFileLoader as ContainerLoader;
use Symfony\Components\DependencyInjection\ContainerBuilder;
use Symfony\Components\Routing\Loader\YamlFileLoader as RoutingLoader;

class LichessKernel extends Kernel
{

    public function registerRootDir()
    {
        return __DIR__;
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Framework\KernelBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Bundle\LichessBundle\LichessBundle()
        );

        return $bundles;
    }

    public function registerBundleDirs()
    {
        return array(
            'Bundle'             => __DIR__.'/../src/Bundle',
            'Symfony\\Framework' => __DIR__.'/../src/vendor/Symfony/src/Symfony/Framework',
        );
    }

    public function registerContainerConfiguration()
    {
        $container = new ContainerBuilder();
        $loader = new ContainerLoader($container, $this->getBundleDirs());

        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');

        if($this->isDebug()) {
            //$configuration->setParameter('profiler.storage.class', 'Bundle\\LichessBundle\\Profiler\\ProfilerStorage');
        }
        else {
            $container->setParameter('exception_handler.controller', 'LichessBundle:Main:notFound');
        }

        return $container;
    }

    public function registerRoutes()
    {
        $loader = new RoutingLoader($this->getBundleDirs());

        return $loader->load(__DIR__.'/config/routing.yml');
    }
}
