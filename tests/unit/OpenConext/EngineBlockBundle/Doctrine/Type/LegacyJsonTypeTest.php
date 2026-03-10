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

class LegacyJsonTypeTest extends TestCase
{
    private MySQLPlatform $platform;

    public static function setUpBeforeClass(): void
    {
        if (!Type::hasType(LegacyJsonType::NAME)) {
            Type::addType(LegacyJsonType::NAME, LegacyJsonType::class);
        }
    }

    public function setUp(): void
    {
        $this->platform = new MySQLPlatform();
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function null_converts_to_null_in_database(): void
    {
        $type = Type::getType(LegacyJsonType::NAME);

        $result = $type->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($result);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function a_value_round_trips_correctly(): void
    {
        $type  = Type::getType(LegacyJsonType::NAME);
        $input = ['foo' => 'bar', 'baz' => [1, 2, 3]];

        $dbValue  = $type->convertToDatabaseValue($input, $this->platform);
        $phpValue = $type->convertToPHPValue($dbValue, $this->platform);

        $this->assertSame($input, $phpValue);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function a_scalar_value_is_encoded_to_json(): void
    {
        $type = Type::getType(LegacyJsonType::NAME);

        $result = $type->convertToDatabaseValue('hello', $this->platform);

        $this->assertSame('"hello"', $result);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function a_float_with_zero_fraction_preserves_decimal_point(): void
    {
        $type = Type::getType(LegacyJsonType::NAME);

        // JSON_PRESERVE_ZERO_FRACTION ensures 1.0 encodes as "1.0", not "1"
        $result = $type->convertToDatabaseValue(1.0, $this->platform);

        $this->assertSame('1.0', $result);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function an_unserializable_value_throws_a_conversion_exception(): void
    {
        $type = Type::getType(LegacyJsonType::NAME);

        // A recursive reference cannot be JSON-encoded and triggers SerializationFailed
        $recursive = [];
        $recursive[] = &$recursive;

        $this->expectException(ConversionException::class);
        $type->convertToDatabaseValue($recursive, $this->platform);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function null_from_database_converts_to_null(): void
    {
        $type = Type::getType(LegacyJsonType::NAME);

        $result = $type->convertToPHPValue(null, $this->platform);

        $this->assertNull($result);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function empty_string_from_database_converts_to_null(): void
    {
        $type = Type::getType(LegacyJsonType::NAME);

        $result = $type->convertToPHPValue('', $this->platform);

        $this->assertNull($result);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function valid_json_from_database_converts_to_php_value(): void
    {
        $type = Type::getType(LegacyJsonType::NAME);

        $result = $type->convertToPHPValue('{"key":"value","num":42}', $this->platform);

        $this->assertSame(['key' => 'value', 'num' => 42], $result);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function invalid_json_from_database_throws_a_conversion_exception(): void
    {
        $type = Type::getType(LegacyJsonType::NAME);

        $this->expectException(ConversionException::class);
        $type->convertToPHPValue('this is not valid json }{', $this->platform);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Doctrine')]
    #[Test]
    public function sql_declaration_uses_clob_not_native_json(): void
    {
        $type = Type::getType(LegacyJsonType::NAME);

        $declaration = $type->getSQLDeclaration([], $this->platform);

        // Must produce longtext, not the native MySQL JSON column type
        $this->assertSame('LONGTEXT', $declaration);
        $this->assertStringNotContainsStringIgnoringCase('json', $declaration);
    }
}
