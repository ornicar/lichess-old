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
            new Symfony\Bundle\CompatAssetsBundle\CompatAssetsBundle(),
            new Bundle\ApcBundle\ApcBundle(),
            new Knplabs\TimeBundle\KnplabsTimeBundle(),
            new Symfony\Bundle\DoctrineMongoDBBundle\DoctrineMongoDBBundle(),
            new Bundle\LichessBundle\LichessBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Bundle\Ornicar\MessageBundle\OrnicarMessageBundle(),
            new Bundle\ForumBundle\ForumBundle(),
            new Application\UserBundle\LichessUserBundle(),
            new Application\MessageBundle\LichessMessageBundle(),
            new Application\ForumBundle\LichessForumBundle()
        );

        if ($this->isDebug()) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function registerBundleDirs()
    {
        return array(
            'Application'          => __DIR__.'/../src/Application',
            'Application\\FOS'     => __DIR__.'/../src/Application/FOS',
            'Application\\Ornicar' => __DIR__.'/../src/Application/Ornicar',
            'Bundle'               => __DIR__.'/../src/Bundle',
            'Bundle\\FOS'          => __DIR__.'/../src/Bundle/FOS',
            'Bundle\\Ornicar'      => __DIR__.'/../src/Bundle/Ornicar',
            'Symfony\\Bundle'      => __DIR__.'/../src/vendor/Symfony/src/Symfony/Bundle',
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
