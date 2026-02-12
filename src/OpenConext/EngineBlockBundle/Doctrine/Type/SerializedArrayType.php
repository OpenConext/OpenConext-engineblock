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

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * This type replaces the deprecated Doctrine 'array' type (which used PHP serialization).
 * It is named SerializedArrayType to avoid confusion with Doctrine's SimpleArrayType (comma-separated).
 */
class SerializedArrayType extends Type
{
    public const NAME = 'array';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if (!isset($column['length'])) {
            $column['length'] = 65535;
        }
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return serialize($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $val = unserialize($value, );

        if ($val === false && $value !== serialize(false)) {
            throw new ConversionException(sprintf(
                'Could not convert database value "%s" to Doctrine Type %s',
                $value,
                $this->getName()
            ));
        }

        return $val;
    }

    public function getName(): string
    {
        return self::NAME;
    }

}
