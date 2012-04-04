<?php

namespace Lichess\OpeningBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This class Configuration the configuration information for the bundle
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

        $treeBuilder->root('lichess_opening', 'array')
            ->children()
                ->arrayNode('feature')->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('config_persistence')->defaultTrue()->end()
                        ->booleanNode('form')->defaultTrue()->end()
                        ->booleanNode('starter')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder->buildTree();
    }
}
