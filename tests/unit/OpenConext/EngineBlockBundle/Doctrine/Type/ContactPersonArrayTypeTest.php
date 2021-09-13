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

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use PHPUnit\Framework\TestCase;

class ContactPersonArrayTypeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var MySqlPlatform
     */
    private $platform;

    /**
     * Register the type, since we're forced to use the factory method.
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function setUpBeforeClass()
    {
        if (!Type::hasType(ContactPersonArrayType::NAME)) {
            Type::addType(ContactPersonArrayType::NAME, ContactPersonArrayType::class);
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
        $contactPersonType = Type::getType(ContactPersonArrayType::NAME);

        $value = $contactPersonType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function contact_person_array_type_converted_to_json()
    {
        $contactPersonType = Type::getType(ContactPersonArrayType::NAME);
        $contactPerson = [new ContactPerson("support")];
        $contactPerson[0]->givenName = "givenName";
        $contactPerson[0]->telephoneNumber = "telephoneNumber";
        $contactPerson[0]->surName = "surName";
        $contactPerson[0]->emailAddress = "emailAddress";

        $value = $contactPersonType->convertToDatabaseValue($contactPerson, $this->platform);

        $this->assertEquals(json_encode($contactPerson), $value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_null_value_is_converted_to_null()
    {
        $contactPersonType = Type::getType(ContactPersonArrayType::NAME);

        $value = $contactPersonType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function saved_object_equals_result()
    {
        $contactPersonType = Type::getType(ContactPersonArrayType::NAME);
        $contactPerson = [new ContactPerson("support")];
        $contactPerson[0]->givenName = "givenName";
        $contactPerson[0]->telephoneNumber = "telephoneNumber";
        $contactPerson[0]->surName = "surName";
        $contactPerson[0]->emailAddress = "emailAddress";

        $value = $contactPersonType->convertToPHPValue($contactPersonType->convertToDatabaseValue($contactPerson, $this->platform),
            $this->platform);

        $this->assertEquals($contactPerson, $value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_php_value_causes_an_exception_upon_conversion()
    {
        $contactPersonType = Type::getType(ContactPersonArrayType::NAME);

        $this->expectException(ConversionException::class);
        $contactPersonType->convertToDatabaseValue(false, $this->platform);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $contactPersonType = Type::getType(ContactPersonArrayType::NAME);

        $this->expectException(ConversionException::class);
        $contactPersonType->convertToPHPValue(false, $this->platform);
    }
}
