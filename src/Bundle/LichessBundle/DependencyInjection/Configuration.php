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
                ->scalarNode('class')->defaultValue('Bundle\\LichessBundle\\Ai\\Crafty')->end()
            ->end()
            ->arrayNode('translation')
                ->addDefaultsIfNotSet()
                ->scalarNode('remote_domain')->defaultValue('lichess.org')->end()
            ->end();

        return $treeBuilder->buildTree();
    }
}
