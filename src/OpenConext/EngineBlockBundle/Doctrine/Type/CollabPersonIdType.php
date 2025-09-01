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
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;

class CollabPersonIdType extends Type
{
    const NAME = 'engineblock_collab_person_id';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // overwrite the fieldDeclaration to always be MAX_LENGTH characters max, this is also enforced in the VO.
        $fieldDeclaration['length'] = CollabPersonId::MAX_LENGTH;

        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return $value;
        }

        if (!$value instanceof CollabPersonId) {
            $valueForMessage = $this->getValueForExceptionMessage($value);
            throw new ConversionException(
                sprintf(
                    'Value "%s" must be null or an instance of CollabPersonId to be able to ' .
                    'convert it to a database value',
                    $valueForMessage
                )
            );
        }

        return $value->getCollabPersonId();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return $value;
        }

        try {
            $entityId = new CollabPersonId($value);
        } catch (InvalidArgumentException $e) {
            // get nice standard message, so we can throw it keeping the exception chain
            $doctrineExceptionMessage = ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                'a valid CollabPersonId'
            )->getMessage();

            throw new ConversionException($doctrineExceptionMessage, 0, $e);
        }

        return $entityId;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * @see https://github.com/doctrine/DoctrineBundle/issues/977#issuecomment-497215968
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform) : bool
    {
        return true;
    }

    private function getValueForExceptionMessage($value): string
    {
        if (is_object($value)) {
            return get_class($value);
        }

        if (is_array($value)) {
            return 'Array';
        }

        return (string)$value;
    }
}
