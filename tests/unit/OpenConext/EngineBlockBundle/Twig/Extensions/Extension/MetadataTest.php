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

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use EngineBlock_Attributes_Metadata;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

class MetadataTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * @var EngineBlock_Attributes_Metadata
     */
    private $metadataDefinition;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    protected function setUp(): void
    {
        // Note that this unit tests depends on a real EngingeBlock_EngineBlock_Attributes_Metadata instance from the
        // Di container.
        $this->metadataDefinition = \EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getAttributeMetadata();
        $this->translator = m::mock(TranslatorInterface::class);
        $this->metadata = new Metadata($this->metadataDefinition, $this->translator);
    }

    /**
     * This test uses the tests/resources/config/attributes-fixture.json fixture
     * @see \EngineBlock_Application_TestDiContainer::getAttributeMetadata
     *
     * @test
     * @group EngineBlock
     */
    public function sort_by_display_order_favors_ordered_items_ungrouped()
    {
        // norEduPersonBirthDate is an unordered item, should be sorted at the bottom
        $attributes = json_decode('{
            "urn:mace:dir:attribute-def:norEduPersonBirthDate": ["1970-01-01"],
            "urn:mace:dir:attribute-def:displayName": ["John Doe"],
            "urn:mace:dir:attribute-def:cn": ["John Doe"],
            "urn:mace:dir:attribute-def:eduPersonPrincipalName": ["j.doe@example.com"]
        }', true);

        $ordered = $this->metadata->sortByDisplayOrder($attributes, []);

        $expectedOrder = [
            "urn:mace:dir:attribute-def:displayName",
            "urn:mace:dir:attribute-def:cn",
            "urn:mace:dir:attribute-def:eduPersonPrincipalName",
            "urn:mace:dir:attribute-def:norEduPersonBirthDate",
        ];

        $this->assertEquals($expectedOrder, array_keys($ordered['idp']));
    }

    /**
     * @test
     * @group EngineBlock
     */
    public function sort_by_display_order_favors_ordered_items_grouped()
    {
        // norEduPersonBirthDate is an unordered item, should be sorted at the bottom
        $attributes = json_decode('{
            "urn:mace:dir:attribute-def:norEduPersonBirthDate": ["1970-01-01"],
            "urn:mace:dir:attribute-def:displayName": ["John Doe"],
            "urn:mace:dir:attribute-def:eduPersonAffiliation": ["SURFnet"],
            "urn:schac:attribute-def:schacPersonalUniqueCode": ["022934029834"],
            "urn:mace:dir:attribute-def:cn": ["John Doe"],
            "urn:mace:surffederatie.nl:attribute-def:nlStudielinkNummer": [735632],
            "urn:mace:dir:attribute-def:eduPersonPrincipalName": ["j.doe@example.com"]
        }', true);

        $attributeSources = [
            "urn:schac:attribute-def:schacPersonalUniqueCode" => "dummySource",
            "urn:mace:dir:attribute-def:eduPersonAffiliation" => "dummySource",
            "urn:mace:dir:attribute-def:norEduPersonBirthDate" => "dummySource",
        ];

        $ordered = $this->metadata->sortByDisplayOrder($attributes, $attributeSources);

        $expectedIdPOrder = [
            "urn:mace:dir:attribute-def:displayName",
            "urn:mace:dir:attribute-def:cn",
            "urn:mace:dir:attribute-def:eduPersonPrincipalName",
            "urn:mace:surffederatie.nl:attribute-def:nlStudielinkNummer",
        ];

        $expectedDummySourceOrder = [
            "urn:mace:dir:attribute-def:eduPersonAffiliation",
            "urn:schac:attribute-def:schacPersonalUniqueCode",
            "urn:mace:dir:attribute-def:norEduPersonBirthDate",
        ];

        $this->assertEquals($expectedIdPOrder, array_keys($ordered['idp']));
        $this->assertEquals($expectedDummySourceOrder, array_keys($ordered['dummySource']));
    }
}
