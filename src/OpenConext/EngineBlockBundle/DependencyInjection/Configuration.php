<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlockBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('open_conext_engine_block');
        $root = $treeBuilder->getRootNode();

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
