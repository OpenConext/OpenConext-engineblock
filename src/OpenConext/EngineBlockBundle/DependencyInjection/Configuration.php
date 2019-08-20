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
        $this->appendErrorFeedbackConfiguration($root);

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

    private function appendErrorFeedbackConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('error_feedback')
                    ->children()
                        ->arrayNode('wiki_links')
                            ->children()
                                ->arrayNode('fallback')
                                    ->isRequired()
                                    ->info('Provide a fallback wiki link that is used when a language/page combination cannot be found.')
                                    ->scalarPrototype()
                                        ->info('Provide a URI to the default/fallback wiki page for this specific error page language combination. 
                                        Please review the example in parameter.yml.dist')
                                    ->end()
                                ->end()
                                ->arrayNode('specified')
                                    ->arrayPrototype()
                                        ->info('Please specify an array of i18n language abbreviation keys, mapped to wiki links matching 
                                        that language. Example: [en => https://wiki.example.com/page1, pt => https://wiki.example.pt/page1].')
                                        ->scalarPrototype()
                                            ->info('Provide a URI to the wiki page for this specific error page language combination. 
                                            Please review the example in parameter.yml.dist')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('idp_contact')
                            ->scalarPrototype()
                                ->info('Specify page identifiers to show the IdP mailto link on.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
