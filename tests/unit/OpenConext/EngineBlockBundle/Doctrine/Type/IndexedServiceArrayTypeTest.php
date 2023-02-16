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
use OpenConext\EngineBlock\Metadata\IndexedService;
use PHPUnit\Framework\TestCase;

class IndexedServiceArrayTypeTest extends TestCase
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
        if (!Type::hasType(IndexedServiceArrayType::NAME)) {
            Type::addType(IndexedServiceArrayType::NAME, IndexedServiceArrayType::class);
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
        $indexedServiceArrayType = Type::getType(IndexedServiceArrayType::NAME);

        $value = $indexedServiceArrayType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function indexed_service_array_type_converted_to_json()
    {
        $indexedServiceArrayType = Type::getType(IndexedServiceArrayType::NAME);
        $serviceIndex = [new IndexedService("location", "binding", 0)];
        $value = $indexedServiceArrayType->convertToDatabaseValue($serviceIndex, $this->platform);

        $this->assertEquals(json_encode($serviceIndex), $value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_null_value_is_converted_to_null()
    {
        $indexedServiceArrayType = Type::getType(IndexedServiceArrayType::NAME);

        $value = $indexedServiceArrayType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function saved_object_equals_result()
    {
        $indexedServiceArrayType = Type::getType(IndexedServiceArrayType::NAME);
        $serviceIndex = [new IndexedService("location", "binding", 0)];

        $value = $indexedServiceArrayType->convertToPHPValue($indexedServiceArrayType->convertToDatabaseValue($serviceIndex, $this->platform),
            $this->platform);

        $this->assertEquals($serviceIndex, $value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_php_value_causes_an_exception_upon_conversion()
    {
        $indexedServiceArrayType = Type::getType(IndexedServiceArrayType::NAME);

        $this->expectException(ConversionException::class);
        $indexedServiceArrayType->convertToDatabaseValue(false, $this->platform);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $indexedServiceArrayType = Type::getType(IndexedServiceArrayType::NAME);

        $this->expectException(ConversionException::class);
        $indexedServiceArrayType->convertToPHPValue(false, $this->platform);
    }
}
