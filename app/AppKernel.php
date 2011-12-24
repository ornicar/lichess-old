<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\ClassLoader\DebugUniversalClassLoader;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Symfony\Bundle\DoctrineMongoDBBundle\DoctrineMongoDBBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),

            new Bundle\ApcBundle\ApcBundle(),
            new Herzult\Bundle\ForumBundle\HerzultForumBundle(),
            new Bundle\LichessBundle\LichessBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOQ\ElasticaBundle\FOQElasticaBundle(),
            new Lichess\ChartBundle\LichessChartBundle(),
            new Lichess\OpeningBundle\LichessOpeningBundle(),
            //new Lichess\SearchBundle\LichessSearchBundle(),
            new Lichess\TranslationBundle\LichessTranslationBundle(),
            new Ornicar\AkismetBundle\OrnicarAkismetBundle(),
            new Ornicar\MessageBundle\OrnicarMessageBundle(),
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),

            new Application\ForumBundle\LichessForumBundle(),
            new Lichess\MessageBundle\LichessMessageBundle(),
            new Application\UserBundle\LichessUserBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\DoctrineFixturesBundle\DoctrineFixturesBundle();
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
