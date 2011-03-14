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
            ->booleanNode('test')->defaultValue(false)->end()
            ->booleanNode('debug_assets')->defaultValue(false)->end()
            ->arrayNode('akismet')
                ->isRequired()
                ->scalarNode('api_key')->end()
                ->scalarNode('url')->end()
            ->end()
            ->arrayNode('ai')
                ->addDefaultsIfNotSet()
                ->arrayNode('crafty')
                    ->addDefaultsIfNotSet()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->scalarNode('priority')->defaultValue(2)->end()
                    ->scalarNode('executable_path')->defaultValue('/usr/bin/crafty')->end()
                    ->scalarNode('book_dir')->defaultValue('/usr/share/crafty')->end()
                ->end()
                ->arrayNode('stupid')
                    ->addDefaultsIfNotSet()
                    ->booleanNode('enabled')->defaultTrue()->end()
                    ->scalarNode('priority')->defaultValue(1)->end()
                ->end()
            ->end()
            ->arrayNode('seek')
                ->addDefaultsIfNotSet()
                ->booleanNode('use_session')->defaultTrue()->end()
            ->end()
            ->arrayNode('anybody_starter')
                ->addDefaultsIfNotSet()
                ->booleanNode('check_creator_is_connected')->defaultTrue()->end()
            ->end()
            ->arrayNode('translation')
                ->addDefaultsIfNotSet()
                ->scalarNode('remote_domain')->defaultValue('lichess.org')->end()
            ->end();

        return $treeBuilder->buildTree();
    }
}
