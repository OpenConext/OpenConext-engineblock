<?php

/**
 * Copyright 2010 SURFnet B.V.
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
use OpenConext\EngineBlock\Metadata\Mdui;

class MetadataMduiType extends Type
{
    const NAME = 'engineblock_metadata_mdui';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // We want a `TEXT` field declaration in our column, the `LONGTEXT` default causes issues when running the
        // DBMS in strict mode.
        $fieldDeclaration['length'] = 65535;
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return $value;
        }

        if (!$value instanceof Mdui) {
            throw new ConversionException(
                sprintf(
                    'Value "%s" must be null or an instance of Mdui to be able to ' .
                    'convert it to a database value',
                    is_object($value) ? get_class($value) : (string)$value
                )
            );
        }

        return $value->toJson();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return $value;
        }

        try {
            $mdui = Mdui::fromJson($value);
        } catch (InvalidArgumentException $e) {
            // get nice standard message, so we can throw it keeping the exception chain
            $doctrineExceptionMessage = ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                'valid serialized mdui json'
            )->getMessage();

            throw new ConversionException($doctrineExceptionMessage, 0, $e);
        }

        return $mdui;
    }

    public function getName()
    {
        return self::NAME;
    }
}
