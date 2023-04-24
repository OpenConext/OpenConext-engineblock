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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Organization;
use PHPUnit\Framework\TestCase;

class EngineBlock_Test_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfoTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSsoDescriptorUiInfoIsCorrectlyAddedToRootElement()
    {
        $entity = $this->factoryEntity();
        $uiInfoMapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo($entity);
        $rootElement = array();

        $expectedRootElement = [
            'md:Extensions' => [
                'mdui:UIInfo' => [
                    [
                        'mdui:DisplayName' => [
                            ['_xml:lang' => 'en', '__v' => 'bogus en value'],
                            ['_xml:lang' => 'nl', '__v' => 'bogus nl value'],
                        ],
                        'mdui:Description' => [
                            ['_xml:lang' => 'en', '__v' => 'bogus en value'],
                            ['_xml:lang' => 'nl', '__v' => 'bogus nl value'],
                        ],
                        'mdui:Keywords' => [
                            [['_xml:lang' => 'en', '__v' => 'bogus en value']],
                            [['_xml:lang' => 'nl', '__v' => 'bogus nl value']],
                        ],
                        'mdui:PrivacyStatementURL' => [
                            ['_xml:lang' => 'en', '__v' => 'https://foobar.en.example.com'],
                            ['_xml:lang' => 'nl', '__v' => 'https://foobar.nl.example.com'],
                        ],
                    ]
                ]
            ]
        ];
        $this->assertEquals($expectedRootElement, $uiInfoMapper->mapTo($rootElement));
    }
    public function testSsoDescriptorUiInfoWithMultipleKeywordsIsCorrectlyAddedToRootElement()
    {
        $entity = $this->factoryEntity(true);
        $uiInfoMapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo($entity);
        $rootElement = array();

        $expectedRootElement = [
            'md:Extensions' => [
                'mdui:UIInfo' => [
                    [
                        'mdui:DisplayName' => [
                            ['_xml:lang' => 'en', '__v' => 'bogus en value'],
                            ['_xml:lang' => 'nl', '__v' => 'bogus nl value'],
                        ],
                        'mdui:Description' => [
                            ['_xml:lang' => 'en', '__v' => 'bogus en value'],
                            ['_xml:lang' => 'nl', '__v' => 'bogus nl value'],
                        ],
                        'mdui:Keywords' => [
                            [['_xml:lang' => 'en', '__v' => 'foobar, stepup, example, com']],
                            [['_xml:lang' => 'nl', '__v' => 'a nice set of dutch keywords no commas']],
                        ],
                        'mdui:PrivacyStatementURL' => [
                            ['_xml:lang' => 'en', '__v' => 'https://foobar.en.example.com'],
                            ['_xml:lang' => 'nl', '__v' => 'https://foobar.nl.example.com'],
                        ],
                    ]
                ]
            ]
        ];
        $this->assertEquals($expectedRootElement, $uiInfoMapper->mapTo($rootElement));
    }

    /**
     * @return IdentityProvider
     */
    private function factoryEntity($multipleKeywords = false)
    {
        $keywordsJson = '"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},';
        if ($multipleKeywords) {
            $keywordsJson = '"Keywords":{"name":"Keywords","values":{"en":{"value":"foobar, stepup, example, com","language":"en"},"nl":{"value":"a nice set of dutch keywords no commas","language":"nl"},"pt":{"value":"","language":"pt"}}},';
        }
        $creationJson = sprintf(
            '{"DisplayName":{"name":"DisplayName","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},%s"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"https://foobar.en.example.com","language":"en"},"nl":{"value":"https://foobar.nl.example.com","language":"nl"}}}}',
            $keywordsJson
        );
        // Create a value object from json
        $mdui = Mdui::fromJson($creationJson);
        $entity = new IdentityProvider('https://idp.example.edu', $mdui);
        $entity->organizationEn = new Organization('English Name', 'English displayname', 'English url');
        $entity->organizationNl = new Organization('Nederlandse naam', 'Nederlandse weergavenaam', 'Nederlandse url');

        return $entity;
    }
}
