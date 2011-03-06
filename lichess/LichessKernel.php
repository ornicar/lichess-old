<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
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
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\ZendBundle\ZendBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Bundle\ApcBundle\ApcBundle(),
            new Knplabs\TimeBundle\KnplabsTimeBundle(),
            new Symfony\Bundle\DoctrineMongoDBBundle\DoctrineMongoDBBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Bundle\Ornicar\MessageBundle\OrnicarMessageBundle(),
            new Bundle\ForumBundle\ForumBundle(),
            new Application\UserBundle\LichessUserBundle(),
            new Application\MessageBundle\LichessMessageBundle(),
            new Application\ForumBundle\LichessForumBundle(),
            new Bundle\LichessBundle\LichessBundle(),
        );

        if ($this->isDebug()) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
