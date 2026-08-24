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
use OpenConext\EngineBlock\Metadata\ContactPerson;

class ContactPersonArrayType extends Type
{
    const NAME = 'engineblock_contact_person_array';

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

        if (!is_array($value)) {
            throw new ConversionException(
                sprintf(
                    'Value "%s" must be null or an instance of %s (%s) to be able to ' .
                    'convert it to a database value',
                    is_array($value) ? 'Array' : (string)$value,
                    $this->getName(),
                    "null, array"
                )
            );
        }

        if (empty($value)) {
            return null;
        }

        foreach ($value as $contactPerson) {
            if (!$contactPerson instanceof ContactPerson) {
                throw new ConversionException(
                    sprintf(
                        'Value "%s" must be null or an instance of %s (%s) to be able to ' .
                        'convert it to a database value',
                        is_object($contactPerson) ? get_class($contactPerson) : (string)$contactPerson,
                        $this->getName(),
                        ContactPerson::class
                    )
                );
            }
        }

        return json_encode($value);
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        if (is_null($value)) {
            return [];
        }

        try {
            $decoded = json_decode($value, true);

            if (!is_array($decoded)) {
                throw new ConversionException(
                    sprintf(
                        'Could not convert database value "%s" to Doctrine Type %s. Expected format: %s',
                        $value,
                        $this->getName(),
                        "array"
                    )
                );
            }

            $contactPersons = [];
            foreach ($decoded as $contactPerson) {
                if (!is_array($contactPerson)) {
                    throw new ConversionException(
                        sprintf(
                            'Could not convert database value "%s" to Doctrine Type %s. Expected format: %s',
                            $contactPerson,
                            $this->getName(),
                            "array"
                        )
                    );
                }

                array_push($contactPersons, ContactPerson::fromArray($contactPerson));
            }
        } catch (InvalidArgumentException $e) {
            throw new ConversionException(
                sprintf(
                    'Could not convert database value "%s" to Doctrine Type %s. Expected format: %s',
                    $value,
                    $this->getName(),
                    ContactPerson::class
                ),
                0,
                $e
            );
        }

        return $contactPersons;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
