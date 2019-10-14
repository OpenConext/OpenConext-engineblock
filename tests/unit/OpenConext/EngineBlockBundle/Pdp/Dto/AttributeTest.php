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

namespace OpenConext\EngineBlockBundle\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlockBundle\Pdp\Dto\Attribute;
use OpenConext\Value\Saml\NameIdFormat;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group Pdp
     */
    public function an_attribute_without_a_datatype_is_serialized_correctly()
    {
        $attribute = new Attribute;
        $attribute->attributeId = NameIdFormat::UNSPECIFIED;
        $attribute->value = 'an-unspecified-name-id';

        $expectedSerializedAttribute = <<<JSON
{
    "AttributeId": "$attribute->attributeId",
    "Value": "$attribute->value"
}
JSON;

        $actualSerializedAttribute = json_encode($attribute, JSON_PRETTY_PRINT);

        $this->assertEquals($expectedSerializedAttribute, $actualSerializedAttribute);
    }

    /**
     * @test
     * @group Pdp
     */
    public function an_attribute_with_a_datatype_is_serialized_correctly()
    {
        $attribute = new Attribute;
        $attribute->attributeId = NameIdFormat::UNSPECIFIED;
        $attribute->value = 'an-unspecified-name-id';
        $attribute->dataType = 'http://www.w3.org/2001/XMLSchema#string';

        $encodedDataType = json_encode($attribute->dataType);

        $expectedSerializedAttribute = <<<JSON
{
    "AttributeId": "$attribute->attributeId",
    "Value": "$attribute->value",
    "DataType": $encodedDataType
}
JSON;

        $actualSerializedAttribute = json_encode($attribute, JSON_PRETTY_PRINT);

        $this->assertEquals($expectedSerializedAttribute, $actualSerializedAttribute);
    }
}
