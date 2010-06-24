<?php

require_once __DIR__.'/../src/autoload.php';
require_once __DIR__.'/../src/vendor/Symfony/src/Symfony/Foundation/bootstrap.php';

use Symfony\Foundation\Kernel;
use Symfony\Components\DependencyInjection\Loader\YamlFileLoader as ContainerLoader;
use Symfony\Components\Routing\Loader\YamlFileLoader as RoutingLoader;
use Symfony\Components\HttpKernel\HttpKernelInterface;
use Symfony\Components\HttpKernel\Request;

class LichessKernel extends Kernel
{

    public function registerRootDir()
    {
        return __DIR__;
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Foundation\Bundle\KernelBundle(),
            new Symfony\Framework\WebBundle\WebBundle(),
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
        $loader = new ContainerLoader($this->getBundleDirs());

        $configuration = $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');

        if($this->isDebug()) {
            $configuration->setParameter('profiler.storage.class', 'Bundle\\LichessBundle\\Profiler\\ProfilerStorage');
        }
        else {
            $configuration->setParameter('exception_handler.controller', 'LichessBundle:Main:notFound');
        }

        $configuration->merge($loader->load(__DIR__.'/config/lichess.yml'));

        return $configuration;
    }

    public function registerRoutes()
    {
        $loader = new RoutingLoader($this->getBundleDirs());

        return $loader->load(__DIR__.'/config/routing.yml');
    }
}
