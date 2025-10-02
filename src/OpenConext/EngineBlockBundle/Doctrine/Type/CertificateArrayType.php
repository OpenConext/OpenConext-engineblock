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
use OpenConext\EngineBlock\Metadata\X509\X509CertificateFactory;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateLazyProxy;

/**
 * @see https://github.com/doctrine/dbal/blob/3.10.x/src/Types/ArrayType.php
 */
class CertificateArrayType extends Type
{
    public const NAME = 'engineblock_certificate_array';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 65535;
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_array($value)) {
            throw new ConversionException(sprintf('Invalid value for %s expected array got %s', $this->getName(), gettype($value)));
        }
        foreach ($value as $cert) {
            if (!$cert instanceof X509CertificateLazyProxy) {
                throw new ConversionException(
                    sprintf(
                        'Invalid certificate element for %s expected %s got %s',
                        $this->getName(),
                        X509CertificateLazyProxy::class,
                        get_debug_type($cert)
                    )
                );
            }
        }

        return serialize($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $unserialized = unserialize($value, ['allowed_classes' => [X509CertificateLazyProxy::class, X509CertificateFactory::class]]);
        if (is_array($unserialized)) {
            return $unserialized;
        }

        throw new ConversionException(sprintf(
            'Invalid format for %s expected serialized array, got: %s',
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
