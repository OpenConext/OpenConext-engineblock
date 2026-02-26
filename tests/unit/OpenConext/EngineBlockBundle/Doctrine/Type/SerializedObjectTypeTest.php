<?php

/**
 * Copyright 2026 SURFnet B.V.
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

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class SerializedObjectTypeTest extends TestCase
{
    private MySQLPlatform $platform;

    public static function setUpBeforeClass(): void
    {
        if (!Type::hasType(SerializedObjectType::NAME)) {
            Type::addType(SerializedObjectType::NAME, SerializedObjectType::class);
        }
    }

    public function setUp(): void
    {
        $this->platform = new MySQLPlatform();
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function null_is_serialized_to_a_string_not_sql_null(): void
    {
        $type = Type::getType(SerializedObjectType::NAME);

        $result = $type->convertToDatabaseValue(null, $this->platform);

        // Mirrors DBAL 3 ObjectType: null is serialize()d to 'N;', never SQL NULL.
        $this->assertSame(serialize(null), $result);
        $this->assertNotNull($result);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function an_object_round_trips_correctly(): void
    {
        $type        = Type::getType(SerializedObjectType::NAME);
        $input       = new stdClass();
        $input->foo  = 'bar';
        $input->list = [1, 2, 3];

        $dbValue  = $type->convertToDatabaseValue($input, $this->platform);
        $phpValue = $type->convertToPHPValue($dbValue, $this->platform);

        $this->assertEquals($input, $phpValue);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function null_from_database_converts_to_null(): void
    {
        $type = Type::getType(SerializedObjectType::NAME);

        $result = $type->convertToPHPValue(null, $this->platform);

        $this->assertNull($result);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function serialized_null_from_database_converts_to_null(): void
    {
        $type = Type::getType(SerializedObjectType::NAME);

        // 'N;' is what old code stored when an object field (e.g. logo) was null
        $result = $type->convertToPHPValue(serialize(null), $this->platform);

        $this->assertNull($result);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function corrupted_database_value_throws_a_conversion_exception(): void
    {
        $type = Type::getType(SerializedObjectType::NAME);

        $this->expectException(ConversionException::class);
        $type->convertToPHPValue('this is not valid serialized data }{', $this->platform);
    }
}
