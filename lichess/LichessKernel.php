<?php

require_once __DIR__.'/../src/autoload.php';

use Symfony\Framework\Kernel;
use Symfony\Components\DependencyInjection\Loader\LoaderInterface;
use Symfony\Components\DependencyInjection\ContainerBuilder;

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
            new Symfony\Bundle\ZendBundle\ZendBundle(),
            new Bundle\LichessBundle\LichessBundle()
        );

        return $bundles;
    }

    public function registerBundleDirs()
    {
        return array(
            'Bundle'             => __DIR__.'/../src/Bundle',
            'Symfony\\Framework' => __DIR__.'/../src/vendor/Symfony/src/Symfony/Framework'
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $container = new ContainerBuilder();

        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');

        $container->setParameter('exception_listener.controller', 'LichessBundle:Main:notFound');

        $container->setParameter('validator.message_interpolator.class', 'Bundle\\LichessBundle\\Validator\\NoValidationXliffMessageInterpolator');

        return $container;
    }
}
