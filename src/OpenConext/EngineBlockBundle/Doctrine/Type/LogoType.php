<?php

/**
 * Copyright 2025 SURFnet B.V.
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
use OpenConext\EngineBlock\Metadata\Logo;

class LogoType extends Type
{
    public const NAME = 'engineblock_logo';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 65535; // prefer TEXT over LONGTEXT
        return $platform->getClobTypeDeclarationSQL($column);
    }

    /**
     * @param Logo|null $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!$value instanceof Logo) {
            throw new ConversionException(sprintf(
                'Invalid value for %s expected %s got %s',
                $this->getName(),
                Logo::class,
                get_debug_type($value)
            ));
        }
        return serialize($value);
    }

    /**
     * @return Logo|null
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Logo
    {
        if ($value === null || $value === '') {
            return null;
        }

        $unserialized = unserialize($value, ['allowed_classes' => [Logo::class]]);
        if ($unserialized instanceof Logo) {
            return $unserialized;
        }

        throw new ConversionException(sprintf(
            'Invalid format for %s expected serialized Logo, got: %s',
            $this->getName(),
            substr((string)$value, 0, 120)
        ));
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
