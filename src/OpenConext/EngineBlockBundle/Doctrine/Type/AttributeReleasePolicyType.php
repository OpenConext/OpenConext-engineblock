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
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use TypeError;

class AttributeReleasePolicyType extends Type
{
    const NAME = 'engineblock_attribute_release_policy';

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

        if (!$value instanceof AttributeReleasePolicy) {
            throw new ConversionException(
                sprintf(
                    'Value "%s" must be null or an instance of %s (%s) to be able to ' .
                    'convert it to a database value',
                    is_object($value) ? get_class($value) : (string)$value,
                    $this->getName(),
                    "null, " . AttributeReleasePolicy::class
                )
            );
        }

        return json_encode($value->getAttributeRules());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?AttributeReleasePolicy
    {
        if (is_null($value)) {
            return $value;
        }

        try {
            $decoded = json_decode($value, true);

            if (!is_array($decoded)) {
                throw new ConversionException(
                    sprintf(
                        'Could not convert database value "%s" to Doctrine Type %s. Expected format: %s',
                        $decoded,
                        $this->getName(),
                        "array"
                    )
                );
            }

            $arp = AttributeReleasePolicy::fromArray($decoded);
        } catch (InvalidArgumentException | TypeError $e) {
            // get nice standard message, so we can throw it keeping the exception chain
            throw new ConversionException(
                sprintf(
                    'Could not convert database value "%s" to Doctrine Type %s. Expected format: %s',
                    $value,
                    $this->getName(),
                    AttributeReleasePolicy::class
                ),
                0,
                $e
            );
        }

        return $arp;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
