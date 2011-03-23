<?php

namespace Bundle\LichessBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Bundle\LichessBundle\DependencyInjection\Compiler\AiPass;

class LichessBundle extends BaseBundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AiPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
