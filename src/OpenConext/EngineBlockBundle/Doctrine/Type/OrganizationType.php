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

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Metadata\Organization;

class OrganizationType extends Type
{
    const NAME = 'engineblock_organization';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (!$value instanceof Organization) {
            throw ConversionException::conversionFailedInvalidType(
                $value,
                $this->getName(),
                ["null", Organization::class]
            );
        }

        return json_encode($value);
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Organization
    {
        if (is_null($value)) {
            return null;
        }

        try {
            $decoded = json_decode($value, true);

            if (!is_array($decoded)) {
                throw ConversionException::conversionFailedFormat(
                    $value,
                    $this->getName(),
                    "array"
                );
            }

            $organization = new Organization($decoded["name"], $decoded["displayName"], $decoded["url"]);
        } catch (InvalidArgumentException $e) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                Organization::class
            );
        }

        return $organization;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
