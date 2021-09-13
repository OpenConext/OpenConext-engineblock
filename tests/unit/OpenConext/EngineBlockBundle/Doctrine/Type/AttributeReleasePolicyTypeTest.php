<?php

/**
 * Copyright 2021 Stichting Kennisnet
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

namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use PHPUnit\Framework\TestCase;

class AttributeReleasePolicyTypeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var MySqlPlatform
     */
    private $platform;

    /**
     * Register the type, since we're forced to use the factory method.
     * @throws DBALException
     */
    public static function setUpBeforeClass()
    {
        if (!Type::hasType(AttributeReleasePolicyType::NAME)) {
            Type::addType(AttributeReleasePolicyType::NAME, AttributeReleasePolicyType::class);
        }
    }

    public function setUp()
    {
        $this->platform = new MySqlPlatform();
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_null_value_remains_null_in_to_sql_conversion()
    {
        $arpType = Type::getType(AttributeReleasePolicyType::NAME);

        $value = $arpType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function attribute_release_policy_converted_to_json()
    {
        $arpType = Type::getType(AttributeReleasePolicyType::NAME);
        $arp = array();
        $arp["uid"] = ["value" => "*", "motiviation" => ""];
        $arp["givenName"] = ["value" => "*", "motiviation" => ""];
        $arp["attribute"] = ["value" => "*", "motiviation" => ""];
        $attributeReleasePolicy = new AttributeReleasePolicy($arp);

        $value = $arpType->convertToDatabaseValue($attributeReleasePolicy, $this->platform);

        $this->assertEquals(json_encode($attributeReleasePolicy->getAttributeRules()), $value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_null_value_is_converted_to_null()
    {
        $arpType = Type::getType(AttributeReleasePolicyType::NAME);

        $value = $arpType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function saved_object_equals_result()
    {
        $arpType = Type::getType(AttributeReleasePolicyType::NAME);

        $arp = array();
        $arp["uid"] = ["value" => "*", "motiviation" => ""];
        $arp["givenName"] = ["value" => "*", "motiviation" => ""];
        $arp["attribute"] = ["value" => "*", "motiviation" => ""];
        $attributeReleasePolicy = new AttributeReleasePolicy($arp);

        $value = $arpType->convertToPHPValue($arpType->convertToDatabaseValue($attributeReleasePolicy, $this->platform),
            $this->platform);

        $this->assertEquals($attributeReleasePolicy, $value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_php_value_causes_an_exception_upon_conversion()
    {
        $arpType = Type::getType(AttributeReleasePolicyType::NAME);

        $this->expectException(ConversionException::class);
        $arpType->convertToDatabaseValue(false, $this->platform);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $arpType = Type::getType(AttributeReleasePolicyType::NAME);

        $this->expectException(ConversionException::class);
        $arpType->convertToPHPValue(false, $this->platform);
    }
}
