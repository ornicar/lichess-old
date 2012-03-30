<?php

namespace Bundle\LichessBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 */
class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\DependencyInjection\Configuration\NodeInterface
     */
    public function getConfigTree()
    {
        $treeBuilder = new TreeBuilder();

        $treeBuilder->root('lichess', 'array')
            ->children()
                ->booleanNode('test')->defaultFalse()->end()
                ->booleanNode('debug_assets')->defaultValue('%kernel.debug%')->end()
                ->arrayNode('lila')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('internal_url')->end()
                    ->end()
                ->end()
                ->arrayNode('sync')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')->defaultValue('/lila')->end()
                        ->scalarNode('latency')->defaultValue(7)->end()
                    ->end()
                ->end()
                ->arrayNode('feature')->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('cheat')->defaultTrue()->end()
                        ->booleanNode('listener')->defaultTrue()->end()
                        ->booleanNode('elo')->defaultTrue()->end()
                        ->booleanNode('chess')->defaultTrue()->end()
                        ->booleanNode('config')->defaultTrue()->end()
                        ->booleanNode('cache')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder->buildTree();
    }
}
