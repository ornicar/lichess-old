<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\ClassLoader\DebugUniversalClassLoader;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

class LichessKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Bundle\ApcBundle\ApcBundle(),
            new Symfony\Bundle\DoctrineMongoDBBundle\DoctrineMongoDBBundle(),
            new Symfony\Bundle\DoctrineFixturesBundle\DoctrineFixturesBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\CommentBundle\FOSCommentBundle(),
            new FOQ\TyperBundle\FOQTyperBundle(),
            new Ornicar\AkismetBundle\OrnicarAkismetBundle(),
            new Ornicar\MessageBundle\OrnicarMessageBundle(),
            new Bundle\ForumBundle\ForumBundle(),
            new Application\UserBundle\LichessUserBundle(),
            new Application\MessageBundle\LichessMessageBundle(),
            new Application\ForumBundle\LichessForumBundle(),
            new Application\CommentBundle\LichessCommentBundle(),
            new Bundle\LichessBundle\LichessBundle(),
            new Lichess\SearchBundle\LichessSearchBundle(),
            new Lichess\TranslationBundle\LichessTranslationBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function init()
    {
        if ($this->debug) {
            ini_set('display_errors', 1);
            error_reporting(-1);

            DebugUniversalClassLoader::enable();
            ErrorHandler::register();
            if ('cli' !== php_sapi_name()) {
                ExceptionHandler::register();
            }
        } else {
            ini_set('display_errors', 0);
        }
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
