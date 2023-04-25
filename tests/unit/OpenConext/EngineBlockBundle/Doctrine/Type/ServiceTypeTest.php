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
use OpenConext\EngineBlock\Metadata\Service;
use PHPUnit\Framework\TestCase;

class ServiceTypeTest extends TestCase
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
        if (!Type::hasType(ServiceType::NAME)) {
            Type::addType(ServiceType::NAME, ServiceType::class);
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
        $serviceType = Type::getType(ServiceType::NAME);

        $value = $serviceType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function service_converted_to_json()
    {
        $serviceType = Type::getType(ServiceType::NAME);
        $service = new Service("location", "binding");

        $value = $serviceType->convertToDatabaseValue($service, $this->platform);

        $this->assertEquals(json_encode($service), $value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_null_value_is_converted_to_null()
    {
        $serviceType = Type::getType(ServiceType::NAME);

        $value = $serviceType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function saved_object_equals_result()
    {
        $serviceType = Type::getType(ServiceType::NAME);
        $serviceComplete = new Service("location", "binding");
        $serviceLocation = new Service("location", null);
        $serviceBinding = new Service(null, "binding");


        $valueComplete = $serviceType->convertToPHPValue(
            $serviceType->convertToDatabaseValue($serviceComplete, $this->platform),
            $this->platform);
        $valueLocation = $serviceType->convertToPHPValue(
            $serviceType->convertToDatabaseValue($serviceLocation, $this->platform),
            $this->platform);
        $valueBinding = $serviceType->convertToPHPValue(
            $serviceType->convertToDatabaseValue($serviceBinding, $this->platform),
            $this->platform);


        $this->assertEquals($serviceComplete, $valueComplete);
        $this->assertEquals($serviceLocation, $valueLocation);
        $this->assertEquals($serviceBinding, $valueBinding);
        $this->assertNotEquals($valueBinding, $valueLocation);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_php_value_causes_an_exception_upon_conversion()
    {
        $serviceType = Type::getType(ServiceType::NAME);

        $this->expectException(ConversionException::class);
        $serviceType->convertToDatabaseValue(false, $this->platform);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $serviceType = Type::getType(ServiceType::NAME);

        $this->expectException(ConversionException::class);
        $serviceType->convertToPHPValue(false, $this->platform);
    }
}
