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
use OpenConext\EngineBlock\Metadata\Organization;
use PHPUnit\Framework\TestCase;

class OrganizationTypeTest extends TestCase
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
        if (!Type::hasType(OrganizationType::NAME)) {
            Type::addType(OrganizationType::NAME, OrganizationType::class);
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
        $organizationType = Type::getType(OrganizationType::NAME);

        $value = $organizationType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function organization_type_converted_to_json()
    {
        $organizationType = Type::getType(OrganizationType::NAME);
        $organization = new Organization("name", "displayName", "url");
        $value = $organizationType->convertToDatabaseValue($organization, $this->platform);

        $this->assertEquals(json_encode($organization), $value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_null_value_is_converted_to_null()
    {
        $organizationType = Type::getType(OrganizationType::NAME);

        $value = $organizationType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function saved_object_equals_result()
    {
        $organizationType = Type::getType(OrganizationType::NAME);
        $organization = new Organization("name", "displayName", "url");

        $value = $organizationType->convertToPHPValue($organizationType->convertToDatabaseValue($organization, $this->platform),
            $this->platform);

        $this->assertEquals($organization, $value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_php_value_causes_an_exception_upon_conversion()
    {
        $organizationType = Type::getType(OrganizationType::NAME);

        $this->expectException(ConversionException::class);
        $organizationType->convertToDatabaseValue(false, $this->platform);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $organizationType = Type::getType(OrganizationType::NAME);

        $this->expectException(ConversionException::class);
        $organizationType->convertToPHPValue(false, $this->platform);
    }
}
