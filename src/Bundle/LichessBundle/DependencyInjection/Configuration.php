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
                ->arrayNode('ai')->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('crafty')->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->scalarNode('priority')->defaultValue(2)->end()
                                ->scalarNode('executable_path')->defaultValue('/usr/bin/crafty')->end()
                                ->scalarNode('book_dir')->defaultValue('/usr/share/crafty')->end()
                            ->end()
                        ->end()
                        ->arrayNode('stupid')->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->scalarNode('priority')->defaultValue(1)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sync')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')->defaultValue('/xhr.php')->end()
                        ->scalarNode('latency')->defaultValue(6)->end()
                        ->scalarNode('delay')->defaultValue(0.2)->end()
                    ->end()
                ->end()
                ->arrayNode('feature')->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('ai')->defaultTrue()->end()
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
