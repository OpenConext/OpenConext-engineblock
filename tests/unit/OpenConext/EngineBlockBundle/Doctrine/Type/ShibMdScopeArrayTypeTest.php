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
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use PHPUnit\Framework\TestCase;

class ShibMdScopeArrayTypeTest extends TestCase
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
        if (!Type::hasType(ShibMdScopeArrayType::NAME)) {
            Type::addType(ShibMdScopeArrayType::NAME, ShibMdScopeArrayType::class);
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
        $shibMdScopeArrayType = Type::getType(ShibMdScopeArrayType::NAME);

        $value = $shibMdScopeArrayType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function shib_md_scope_array_type_converted_to_json()
    {
        $shibMdScopeArrayType = Type::getType(ShibMdScopeArrayType::NAME);
        $shibMdScopeArray = [new ShibMdScope()];
        $shibMdScopeArray[0]->regexp = true;
        $shibMdScopeArray[0]->allowed = "query";

        $value = $shibMdScopeArrayType->convertToDatabaseValue($shibMdScopeArray, $this->platform);

        $this->assertEquals(json_encode($shibMdScopeArray), $value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_null_value_is_converted_to_null()
    {
        $shibMdScopeArrayType = Type::getType(ShibMdScopeArrayType::NAME);

        $value = $shibMdScopeArrayType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function saved_object_equals_result()
    {
        $shibMdScopeArrayType = Type::getType(ShibMdScopeArrayType::NAME);
        $shibMdScopeArray = [new ShibMdScope()];
        $shibMdScopeArray[0]->regexp = true;
        $shibMdScopeArray[0]->allowed = "query";

        $value = $shibMdScopeArrayType->convertToPHPValue($shibMdScopeArrayType->convertToDatabaseValue($shibMdScopeArray, $this->platform),
            $this->platform);

        $this->assertEquals($shibMdScopeArray, $value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_php_value_causes_an_exception_upon_conversion()
    {
        $shibMdScopeArrayType = Type::getType(ShibMdScopeArrayType::NAME);

        $this->expectException(ConversionException::class);
        $shibMdScopeArrayType->convertToDatabaseValue(false, $this->platform);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $shibMdScopeArrayType = Type::getType(ShibMdScopeArrayType::NAME);

        $this->expectException(ConversionException::class);
        $shibMdScopeArrayType->convertToPHPValue(false, $this->platform);
    }
}
