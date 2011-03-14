<?php

namespace Bundle\LichessBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use InvalidArgumentException;

/**
 * Registers the AIs.
 */
class AiPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('lichess.ai.chain')) {
            return;
        }

        $ais = array();
        foreach ($container->findTaggedServiceIds('lichess.ai') as $id => $attributes) {
            if (!isset($attributes[0]['alias'])) {
                throw new InvalidArgumentException('The AI must have an alias');
            }
            if (!$container->getParameter('lichess.ai.'.$attributes[0]['alias'].'.enabled')) {
                continue;
            }
            $priority = $container->getParameter('lichess.ai.'.$attributes[0]['alias'].'.priority');
            $ais[$priority][] = new Reference($id);
        }

        // sort by priority and flatten
        krsort($ais);
        $ais = call_user_func_array('array_merge', $ais);

        $container->getDefinition('lichess.ai.chain')->setArgument(0, $ais);
    }
}
