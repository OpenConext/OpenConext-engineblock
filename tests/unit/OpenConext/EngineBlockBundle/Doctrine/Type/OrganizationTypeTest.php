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
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Organization;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OrganizationTypeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var MySQLPlatform
     */
    private $platform;

    /**
     * Register the type, since we're forced to use the factory method.
     * @throws DBALException
     */
    public static function setUpBeforeClass(): void
    {
        if (!Type::hasType(OrganizationType::NAME)) {
            Type::addType(OrganizationType::NAME, OrganizationType::class);
        }
    }

    public function setUp(): void
    {
        $this->platform = new MySQLPlatform();
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function a_null_value_remains_null_in_to_sql_conversion()
    {
        $organizationType = Type::getType(OrganizationType::NAME);

        $value = $organizationType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function organization_type_with_all_fields_converted_to_json()
    {
        $organizationType = Type::getType(OrganizationType::NAME);
        $organization = new Organization("name", "displayName", "url");
        $value = $organizationType->convertToDatabaseValue($organization, $this->platform);

        $this->assertEquals(json_encode($organization), $value);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function organization_with_null_values_filters_them_out()
    {
        $organizationType = Type::getType(OrganizationType::NAME);
        $organization = new Organization("name", null, "url");
        $value = $organizationType->convertToDatabaseValue($organization, $this->platform);

        // Should not contain the null displayName field
        $decoded = json_decode($value, true);
        $this->assertArrayNotHasKey('displayName', $decoded);
        $this->assertArrayHasKey('name', $decoded);
        $this->assertArrayHasKey('url', $decoded);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function organization_with_all_null_values_returns_null()
    {
        $organizationType = Type::getType(OrganizationType::NAME);
        $organization = new Organization(null, null, null);
        $value = $organizationType->convertToDatabaseValue($organization, $this->platform);

        // All null values should result in NULL being stored
        $this->assertNull($value);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function a_null_value_is_converted_to_organization_with_all_nulls()
    {
        $organizationType = Type::getType(OrganizationType::NAME);

        $value = $organizationType->convertToPHPValue(null, $this->platform);

        $this->assertInstanceOf(Organization::class, $value);
        $this->assertNull($value->name);
        $this->assertNull($value->displayName);
        $this->assertNull($value->url);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function organization_with_partial_null_values_roundtrips_correctly()
    {
        $organizationType = Type::getType(OrganizationType::NAME);
        $organization = new Organization("name", null, "url");

        $databaseValue = $organizationType->convertToDatabaseValue($organization, $this->platform);
        $value = $organizationType->convertToPHPValue($databaseValue, $this->platform);

        // After roundtrip, should have the non-null values
        $this->assertEquals("name", $value->name);
        $this->assertNull($value->displayName);
        $this->assertEquals("url", $value->url);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function saved_object_with_all_fields_equals_result()
    {
        $organizationType = Type::getType(OrganizationType::NAME);
        $organization = new Organization("name", "displayName", "url");

        $value = $organizationType->convertToPHPValue($organizationType->convertToDatabaseValue($organization, $this->platform),
            $this->platform);

        $this->assertEquals($organization, $value);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function an_invalid_php_value_causes_an_exception_upon_conversion()
    {
        $organizationType = Type::getType(OrganizationType::NAME);

        $this->expectException(ConversionException::class);
        $organizationType->convertToDatabaseValue(false, $this->platform);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $organizationType = Type::getType(OrganizationType::NAME);

        $this->expectException(ConversionException::class);
        $organizationType->convertToPHPValue(false, $this->platform);
    }
}
