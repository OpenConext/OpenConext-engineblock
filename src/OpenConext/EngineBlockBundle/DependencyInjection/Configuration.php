<?php

namespace OpenConext\EngineBlockBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('open_conext_engine_block');

        $this->appendFeatureConfiguration($root);

        return $treeBuilder;
    }

    public function appendFeatureConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('features')
                    ->prototype('boolean')
                        ->example('some.feature.key: true')
                        ->info('Allows configuring a feature, identified by the feature key as enabled or disabled')
                    ->end()
                ->end()
            ->end();
    }
}
