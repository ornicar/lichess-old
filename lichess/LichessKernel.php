<?php

require_once __DIR__.'/../src/autoload.php';

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LichessKernel extends Kernel
{
    /**
     * Switch to a forum environment if the url starts with /forum
     */
    public function boot()
    {
        if(0 !== strncmp($this->environment, 'forum_', 6)) {
            $this->checkForumEnvironment();
        }

        return parent::boot();
    }

    public function registerRootDir()
    {
        return __DIR__;
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\ZendBundle\ZendBundle(),
            new Bundle\ApcBundle\ApcBundle(),
            new Bundle\LichessBundle\LichessBundle()
        );

        if($this->isForumEnvironment()) {
            $bundles = array_merge($bundles, array(
                new Symfony\Bundle\DoctrineMongoDBBundle\DoctrineMongoDBBundle(),
                new Bundle\TimeBundle\TimeBundle(),
                new Bundle\ForumBundle\ForumBundle(),
                new Application\ForumBundle\ForumBundle()
            ));
        }

        if ($this->isDebug()) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    protected function isForumEnvironment()
    {
        return strncmp($this->environment, 'forum_', 6) === 0;
    }

    protected function checkForumEnvironment()
    {
        if(!empty($_SERVER['PATH_INFO'])) {
            $url = $_SERVER['PATH_INFO'];
        } elseif(!empty($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
        } else {
            $url = false;
        }

        if(false === $url || 0 === strncmp($url, '/forum', 6)) {
            if(0 !== strncmp($this->environment, 'forum_', 6)) {
                $this->environment = 'forum_'.$this->environment;
            }
        }
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
